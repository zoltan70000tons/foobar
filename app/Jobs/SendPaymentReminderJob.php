<?php

namespace App\Jobs;

use App\Enums\GlobalLog\LogActionBooking;
use App\Models\Booking;
use App\Models\Installment;
use App\Models\Passenger;
use App\Support\GlobalLogger;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\PDFService;

class SendPaymentReminderJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $bookingId;

    protected string $tz = 'America/Los_Angeles';

    protected ?Carbon $now = null;

    public function __construct(int $bookingId, ?Carbon $now = null) {
        $this->bookingId = $bookingId;
        $this->now = $now ? $now->copy()->timezone($this->tz) : null;
        $this->onQueue('payment-reminder');
    }

    public function handle(): void {
        $booking = Booking::with('passengers')->find($this->bookingId);
        if (!$booking) {
            Log::warning("Booking {$this->bookingId} not found. Skipping reminders.");
            return;
        }

        $emailService = app()->make('App\Services\EmailTemplateService');
        $pdfService = app()->make(PDFService::class);

        $now = $this->now ?? now($this->tz);
        $windowMinutes = 48 * 60;

        foreach ($booking->passengers as $passenger) {
            $status = $passenger->getInstallmentStatus();
            $next = $status['next_installment'] ?? null;
            $fullyPaid = (bool) ($status['fully_paid'] ?? false);
            Log::info(
                "Passenger #{$passenger->id} next installment: " . json_encode($next) . ", fullyPaid={$fullyPaid}",
            );

            if (!$next || empty($next['due_date']) || $fullyPaid) {
                continue;
            }

            $statusStr = strtolower($next['status'] ?? '');
            if (in_array($statusStr, ['paid'])) {
                continue;
            }

            $dueDate = Carbon::parse($next['due_date'], $this->tz);

            $rawLast = $next['last_reminder_sent_at'] ?? null;
            $alreadySent = !blank($rawLast) && $rawLast !== '0000-00-00 00:00:00';

            $minutes = $now->diffInMinutes($dueDate, false);
            if ($minutes < 0 || $minutes > $windowMinutes || $alreadySent) {
                continue;
            }

            $templateId = $this->resolveTemplateId($booking, $passenger);
            if (!$templateId) {
                Log::error(
                    "No template for passenger #{$passenger->id} (lang={$passenger->language}, method={$passenger->payment_method}).",
                );
                continue;
            }

            $locale = $passenger->language ?? ($booking->language ?? Config::get('app.locale', 'en'));
            app()->setLocale($locale);

            $installmentId = $next['installment_id'] ?? null;
            if ($installmentId) {
                $affected = Installment::query()
                    ->where('id', $installmentId)
                    ->whereNull('last_reminder_sent_at')
                    ->update(['last_reminder_sent_at' => $now]);

                if ($affected !== 1) {
                    Log::info("Atomic guard skipped duplicate for installment #{$installmentId}");
                    continue;
                }
            }

            try {
                $content = $emailService->getProcessedTemplate($booking->id, $templateId, $passenger, [
                    'installment_due_date' => $dueDate->isoFormat('LL'),
                    'booking_code' => $booking->booking_code ?? '',
                    'payment_link' => $booking->payment_link ?? null,
                ]);

                $subject = DB::table('email_templates')->where('id', $templateId)->value('subject');

                if (!$subject) {
                    if ($installmentId) {
                        Installment::where('id', $installmentId)->update(['last_reminder_sent_at' => null]);
                    }
                    Log::error("Missing subject for template {$templateId} (booking #{$booking->id})");
                    continue;
                }

                $subject .= ' ' . ($booking->booking_code ?? '');
                $to = $passenger->email ?? null;
                if (!$to) {
                    if ($installmentId) {
                        Installment::where('id', $installmentId)->update(['last_reminder_sent_at' => null]);
                    }
                    Log::error("Passenger #{$passenger->id} has no email.");
                    continue;
                }
                $pdfBinary = null;
                $pdfName = null;

                if ($booking->payment_method === 'BANK_TRANSFER') {
                    Log::info("Generating invoice PDF for booking #{$booking->id}, passenger #{$passenger->id}.");
                    $pdfBinary = $pdfService->generateInvoicePDF($booking, $passenger->language ?? 'en');
                    $pdfName = "Invoice_{$booking->booking_code}.pdf";
                }
                Mail::send([], [], function ($m) use ($to, $subject, $content, $pdfBinary, $pdfName) {
                    $m->to($to)->bcc(env('MAIL_BCC'))->subject($subject)->html($content);
                    if ($pdfBinary instanceof \Barryvdh\DomPDF\PDF) {
                        $m->attachData($pdfBinary->output(), $pdfName, ['mime' => 'application/pdf']);
                    }
                });

                GlobalLogger::log(
                    LogActionBooking::PAYMENT_REMINDER_SENT,
                    'booking',
                    $booking->id,
                    "Reminder sent to {$to} by system",
                    [
                        'additional' => [
                            'paymentPlan' => $booking->payment_plan,
                            'installmentId' => $installmentId,
                            'due' => $dueDate->toDateString(),
                        ],
                    ],
                );

                Log::info(
                    "Reminder sent to {$to} (booking #{$booking->id}, passenger #{$passenger->id}, tpl {$templateId}).",
                );
            } catch (\Throwable $e) {
                if ($installmentId) {
                    Installment::where('id', $installmentId)->update(['last_reminder_sent_at' => null]);
                }
                Log::error(
                    "Error sending reminder (booking #{$booking->id}, passenger #{$passenger->id}): {$e->getMessage()}",
                );
            }
        }
    }

    protected function resolveTemplateId($booking, Passenger $passenger): ?int {
        $payment_method = strtoupper((string) $passenger->payment_method); // CREDIT_CARD | BANK_TRANSFER
        $lang = $passenger->language ?? Config::get('app.locale');

        $map = [
            'de' => ['CREDIT_CARD' => 21, 'BANK_TRANSFER' => 22],
            'es' => ['CREDIT_CARD' => 78, 'BANK_TRANSFER' => 79],
            'en' => ['CREDIT_CARD' => 38, 'BANK_TRANSFER' => 39],
        ];

        return $map[$lang][$payment_method] ?? null;
    }
}
