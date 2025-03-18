<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Models\Passenger;
use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $templateId;
    protected $booking;
    protected $passenger;
    protected $attachments;
    protected $extraData;
    protected $bookingPdf;
    protected $eventImage;

    protected $ticketContract;

    /**
     * Create a new job instance.
     */
    public function __construct(
        int $templateId,
        Booking $booking,
        Passenger $passenger,
        array $attachments = [],
        array $extraData = [],
        bool $bookingPdf = false,
        bool $eventImage = false,
        bool $ticketContract = false
    ) {
        $this->templateId = $templateId;
        $this->booking = $booking;
        $this->passenger = $passenger;
        $this->attachments = $attachments;
        $this->extraData = $extraData;
        $this->bookingPdf = $bookingPdf;
        $this->eventImage = $eventImage;
        $this->ticketContract = $ticketContract;
    }

    /**
     * Execute the job.
     */
    public function handle(EmailTemplateService $emailService)
    {
        try {
            Log::info("Processing email for: " . $this->passenger->email);

            // Get email content
            $content = $emailService->getProcessedTemplate(
                $this->booking->id,
                $this->templateId,
                $this->passenger,
                $this->extraData
            );

            // Get email subject
            $subject = \DB::table('email_templates')->where('id', $this->templateId)->value('subject');

            // Attach booking PDF if necessary
            if ($this->bookingPdf) {
                $pdfService = new \App\Services\PDFService();
                $pdf = $pdfService->generateBookingConfirmationPDF($this->booking);

                if ($pdf instanceof \Barryvdh\DomPDF\PDF) {
                    $pdfPath = storage_path('app/temp_booking_' . $this->booking->id . '.pdf');
                    $pdf->save($pdfPath);
                    $this->attachments[] = [
                        'path' => $pdfPath,
                        'name' => $this->booking->booking_code . '.pdf',
                        'mime' => 'application/pdf'
                    ];
                } else {
                    Log::warning("It wasn't possible to generate PDF: " . $this->booking->id);
                }
            }

            // Attach event image if necessary
            if ($this->eventImage) {
                $eventImageUrl = $this->booking->event->image;
                if (filter_var($eventImageUrl, FILTER_VALIDATE_URL)) {
                    $imageData = @file_get_contents($eventImageUrl);
                    if ($imageData !== false) {
                        $mimeType = get_headers($eventImageUrl, 1)["Content-Type"] ?? 'image/jpeg';
                        $this->attachments[] = [
                            'data' => $imageData,
                            'name' => basename($eventImageUrl),
                            'mime' => $mimeType,
                        ];
                    } else {
                        Log::warning("It wasn't possible to get image for event: " . $this->booking->id);
                    }
                } else {
                    Log::warning("Not valid image URL: " . $this->booking->id);
                }
            }

            $contractPath = storage_path('app/contracts/70000TONS_OF_METAL_2025_TICKET_CONTRACT.pdf');
            if ($this->ticketContract) {
            $contractPath = storage_path('app/contracts/70000TONS_OF_METAL_2025_TICKET_CONTRACT.pdf');
            if (file_exists($contractPath)) {
                $this->attachments[] = [
                    'path' => $contractPath,
                    'name' => '70000TONS_OF_METAL_2025_TICKET_CONTRACT.pdf',
                    'mime' => 'application/pdf'
                ];
            } else {
                Log::warning("File not found in storage.");
            }
        }

            // sending email
            Mail::send([], [], function ($message) use ($subject, $content) {
                $message->to($this->passenger->email)
                    ->subject($subject)
                    ->html($content);

                // attach files
                foreach ($this->attachments as $file) {
                    if (isset($file['path'])) {
                        $message->attach($file['path'], [
                            'as' => $file['name'],
                            'mime' => $file['mime']
                        ]);
                    } elseif (isset($file['data'])) {
                        $message->attachData($file['data'], $file['name'], ['mime' => $file['mime']]);
                    }
                }
            });

            // Delete temp PDF file if exists
            if (isset($pdfPath) && file_exists($pdfPath)) {
                unlink($pdfPath);
            }

            Log::info("Email sent successly: " . $this->passenger->email);
        } catch (\Exception $e) {
            Log::error("Error sending email {$this->passenger->email}: " . $e->getMessage());
        }
    }
}
