<?php

namespace App\Services;

use App\Enums\GlobalLog\LogActionBooking;
use App\Jobs\SendEmailJob;
use App\Models\Booking;
use App\Models\Passenger;
use App\Support\GlobalLogger;
use Blade;
use DB;
use Exception;
use Log;
use NumberToWords\NumberToWords;

class EmailTemplateService
{
    /**
     * get processed template
     *
     * @param Booking id;
     * @param int $templateId
     * @param Passenger $passenger
     * @param array $extraData (optional)
     * @return string
     */
    public function getProcessedTemplate(int $bookingId, int $templateId, Passenger $passenger = null, array $extraData = []): string
    {
        $booking = Booking::with(['cabin.cabinSpec', 'passengers'])->find($bookingId);
        $lang = DB::table('email_templates')->where('id', $templateId)->value('lang');
        if (!$booking) return '';

        switch ($lang) {
            case 'es':
                $footer_template = '70000TONS_email_footer_ESP';
                $header_template = '70000TONS_email_header_ESP';
                break;
            case 'de':
                $footer_template = '70000TONS_email_footer_DEU';
                $header_template = '70000TONS_email_header_DEU';
                break;
            default:
                $footer_template = '70000TONS_email_footer_ENG';
                $header_template = '70000TONS_email_header_ENG';
                break;
        }

        $bodyContent = DB::table('email_templates')->where('id', $templateId)->value('body');
        $data = [
            'header' => DB::table('email_templates')->where('name', '=', $header_template)->value('body'),
            'footer' => DB::table('email_templates')->where('name', '=', $footer_template)->value('body'),
            'passenger' => $passenger,
            'addPassengerLink' => env('FRONTEND_URL') . '/dashboard/my-bookings',
            'makePaymentLink' => env('FRONTEND_URL') . '/make-a-payment',
        ];

        $processedBody = Blade::render($bodyContent, $data);

        $placeholders = $this->extractPlaceholders($processedBody);
        $values = $this->fetchPlaceholderValues($placeholders, $booking, $passenger, $extraData, $lang);
        $processedBody = $this->replacePlaceholders($processedBody, $values);

        return $processedBody;
    }


    /**
     * Extract placeholders from the template
     *
     * @param string $template
     * @return array
     */
    private function extractPlaceholders(string $template): array
    {
        preg_match_all('/\{([\w\.]+)\}/', $template, $matches);
        return $matches[1] ?? [];
    }

    /**
     * search for values of placeholders in DB or extra data
     *
     * @param array $placeholders
     * @param Booking $booking
     * @param Passenger $passenger
     * @param array $extraData
     * @return array
     */
    private function fetchPlaceholderValues(array $placeholders, Booking $booking, Passenger $passenger, array $extraData = [], $lang): array
    {
        // Booking and passenger details
        $CDN_URL = env('AWS_ASSETS_CDN', 'https://d24lxyxdohunaw.cloudfront.net');
        $eventLocation = $booking->event->location ?? '';
        $cabinType = $booking->cabinType->cabin_type ?? '';
        $bookingCode = $booking->booking_code ?? '';
        $paymentPlan = $booking->payment_plan ?? '';
        $grandTotal = $booking->getGrandTotal() ?? 0;
        $individualTotal = $passenger->passenger_allocated_cost ?? 0;
        $passengerOnboardCredit = $passenger->getOnboardCredit();
        $category = $booking->cabin->category->title ?? '';
        $order = getPassengerOrderLabel($passenger->passenger_order, $lang);
        $refunds = $passenger->getRefoundAmount();
        $dollars = floor($refunds);
        $cents = round(($refunds - $dollars) * 100);
        $numberToWords = new NumberToWords();
        $transformer = $numberToWords->getNumberTransformer($lang);
        $words = strtoupper($transformer->toWords($dollars));
        $centsFormatted = str_pad($cents, 2, '0', STR_PAD_LEFT);
        switch ($lang) {
            case 'es':
                $amountInWords = "{$words} CON {$centsFormatted}/100; DÓLARES ESTADOUNIDENSES";
                break;
            case 'en':
                $amountInWords = "{$words} AND {$centsFormatted}/100; UNITED STATES DOLLARS";
                break;
            case 'de':
                $amountInWords = "{$words} UND {$centsFormatted}/100; US-DOLLAR";
                break;
            default:
                $amountInWords = "{$words} AND {$centsFormatted}/100; UNITED STATES DOLLARS";
        }

        $capacity = $booking->cabin->cabinSpec->capacity ?? '';

        // Installment data
        $paymentData = $passenger->installment_status ?? [];
        $nextInstallmentAmountRaw = $paymentData['next_installment']['amount_due'] ?? '';
        $nextInstallmentDateRaw = $paymentData['next_installment']['due_date'] ?? '';
        $isOverdue = false;
        if (!empty($nextInstallmentDateRaw)) {
            $dueDate = \Carbon\Carbon::parse($nextInstallmentDateRaw);
            $today   = \Carbon\Carbon::today();
            $isOverdue = $dueDate->lt($today); 
        }

        // Adjust totals if on installment plan
        if ($paymentPlan === 'INSTALLMENTS') {
            $paymentCount = $passenger->installments()->where('type', 'PAYMENT')->count();
            if ($paymentCount > 0) {
                $grandTotal /= $paymentCount;
                $individualTotal /= $paymentCount;
            }
        }


        // Pre-format values
        $formattedGrandTotal = formatCurrency($grandTotal, true, $lang) ?? '';
        $formattedIndividualTotal = formatCurrency($individualTotal, true, $lang) ?? '';
        $formattedNextInstallmentAmount = formatCurrency($nextInstallmentAmountRaw, false, $lang) ?? '';
        $formattedNextInstallmentDate = formatDate($nextInstallmentDateRaw, false, $lang) ?? '';
        $passengerName = capitalizeWords($passenger->first_name ?? '');
        $formatedOnboardCredit = formatCurrency($passengerOnboardCredit, true,$lang) ?? '';
        $formatRefund = formatCurrency($refunds, true, $lang) ?? '';
        $formatedTicketPrice = formatCurrency($booking->cabin->category->price, true,$lang) ?? '';
        $firstChunk = 10000;
        $firstChunkFormated = '';
        $secondChunkFormated = '';
        if ($grandTotal > $firstChunk) {
            $firstChunkFormated = formatCurrency($firstChunk, true,$lang);
            $secondChunkFormated = formatCurrency($grandTotal - $firstChunk, true, $lang);
        }

        

        // Map placeholder values
        $lookup = [
            'LOGO_IMAGE_URL'         => $CDN_URL . '/logos/70K_Logo_Claim_BW_HiRes.jpg',
            'EVENT_LOCATION'         => $eventLocation,
            'BOOKING_CODE'           => $bookingCode,
            'PASSENGER_NAME'         => $passengerName,
            'GRAND_TOTAL'            => $formattedGrandTotal,
            'INDIVIDUAL_TOTAL'       => $formattedIndividualTotal,
            'CABIN_TYPE'             => $cabinType,
            'PAYMENT_PLAN'           => $paymentPlan,
            'NEXT_INSTALLMENT_DATE'  => $formattedNextInstallmentDate,
            'NEXT_INSTALLMENT_AMOUNT' => $formattedNextInstallmentAmount,
            'ONBOARD_CREDIT'         => $formatedOnboardCredit,
            'CATEGORY'               => $category ?? '',
            'REFUND'                 => $formatRefund,
            'REFUND_IN_WORDS'       => $amountInWords,
            'PASSENGER_ORDER'       => $order,
            'FIRST_CHUNK'           => $firstChunkFormated,
            'SECOND_CHUNK'          => $secondChunkFormated,
            'CAPACITY'              => $capacity,
            'TICKET_PRICE'          => $formatedTicketPrice,
        ];
            $outs = $this->buildOutstandingPlaceholders($booking, $lang);
            $nextInstallmentText = $isOverdue
                ? __('passengers.due_immediately')
                : __('passengers.next_installment', ['date' => $formattedNextInstallmentDate]);
            
            $lookup['OUTSTANDING_RECIPIENTS'] = $outs['OUTSTANDING_RECIPIENTS'];
            $lookup['OUTSTANDING_PASSENGER_BREAKDOWN'] = $outs['OUTSTANDING_PASSENGER_BREAKDOWN'];
            $lookup['NEXT_INSTALLMENT_TEXT'] = $nextInstallmentText;

        // Build the output values
        $values = [];
        foreach ($placeholders as $placeholder) {
            $values[$placeholder] = $lookup[$placeholder] ?? $extraData[$placeholder] ?? '';
        }

        return $values;
    }

    /**
     * Replace placeholders in the template
     *
     * @param string $template
     * @param array $variables
     * @return string
     */
    private function replacePlaceholders(string $template, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $template = str_replace("{" . $key . "}", $value, $template);
        }
        return $template;
    }


    public function sendEmail(int $templateId, Booking $booking, Passenger $passenger, array $attachments = [], array $extraData = [], bool $bookingPdf = false, bool $eventImage = false, $ticketContrac = false, $emailContent = '', $subject = ''): bool
    {
        try {
            $to = $booking->passengers
                ->pluck('email')
                ->filter(fn($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
                ->values()
                ->toArray();
            SendEmailJob::dispatch($templateId, $booking, $passenger, $attachments, $extraData, $bookingPdf, $eventImage, $ticketContrac, $emailContent, $subject, $to)
                ->onQueue('emails');

            GlobalLogger::log(
                LogActionBooking::EMAIL_SENT,
                'booking',
                $booking->id,
                sprintf("Email sent to %s", $passenger->email),
                [
                    'after' => [
                        'booking_id' => $booking->id,
                        'booking_request_id' => $booking->booking_request_id,
                        'template_id' => $templateId,
                        'passenger_id' => $passenger->id,
                        'subject' => $subject,
                        'content' => $emailContent,
                    ],
                ]
            );
            return true;
        } catch (Exception $e) {
            \Log::error("Error sending email to job: " . $e->getMessage());
            return false;
        }
    }



    public function getTemplateId($language, $template)
    {
        $templates = [
            'es' => [
                '+6000'  => 61,
                '-6000'  => 62,
                'single' => 63,
                'invoice' => 64,
                'new'    => 65,
                'comp'   => 66,
                'updated' => 67,
                'thanks_payment_full' => 76,
                'thanks_payment_inst' => 77,
            ],
            'en' => [
                '+6000'  => 52,
                '-6000'  => 53,
                'single' => 54,
                'invoice' => 55,
                'new' => 56,
                'comp'   => 57,
                'updated' => 58,
                'thanks_payment_full'  => 51,
                'thanks_payment_inst'  => 50,
            ],
            'de' => [
                '+6000'  => 3,
                '-6000'  => 4,
                'single' => 5,
                'invoice' => 6,
                'comp'   => 8,
                'new' => 7,
                'updated' => 9,
                'thanks_payment_full' => 19,
                'thanks_payment_inst'  => 20,
            ],
        ];

        return $templates[$language][$template] ?? null;
    }

    private function buildOutstandingPlaceholders(Booking $booking, string $lang): array
    {
        $passengers = $booking->passengers;
        $outstanding = [];
        $breakdownLines = [];
        $event = $booking->event;
        $title = $event->name ?? '';

        foreach ($passengers as $p) {

            $status = $p->getInstallmentStatus();
            $next = $status['next_installment'] ?? null;
            if (!$next || ($status['fully_paid'] ?? false)) {
                continue;
            }

            $amountDue = $next['amount_due'] ?? 0;
            if ($amountDue <= 0) continue;

            $dueDate = isset($next['due_date']) ? new \DateTime($next['due_date']) : null;
            $today   = new \DateTime('today');

            if ($dueDate === null || $dueDate > $today) {
                continue;
            }

            $name = trim($p->first_name);
            if (!$name) {
                $name = getPassengerOrderLabel($p->passenger_order, $lang, 'words');
            } else {
                $name = ucfirst(strtolower(trim($p->first_name)));
            }

            $outstanding[] = $name;
            $formatted = 'USD '.formatCurrency($amountDue, true, $lang);
            $dueLabel = __('passengers.due');
            $breakdownLines[] = "{$name}, {$dueLabel} {$formatted}";
        }

        if (count($outstanding) === 0) {
            return [
                'OUTSTANDING_RECIPIENTS' => '',
                'OUTSTANDING_PASSENGER_BREAKDOWN' => '',
            ];
        }

        if (count($outstanding) === 1) {
            $recipientText = $outstanding[0];
        } else {
            $and = $lang === 'es' ? ' y ' : ($lang === 'de' ? ' en ' : ' and ');
            $last = array_pop($outstanding);
            $recipientText = implode(', ', $outstanding) . " {$and} {$last}";
        }

        $prefix = __('passengers.no_payment_prefix');
        $for = __('passengers.for', ['title' => $title]);

        $recipientsOutput = "{$prefix} {$recipientText} {$for}.";
        $breakdownLines =implode("<br>", $breakdownLines);
        $breakdownLines = $breakdownLines.'<br /><br />';

        return [
            'OUTSTANDING_RECIPIENTS' => $recipientsOutput,
            'OUTSTANDING_PASSENGER_BREAKDOWN' => $breakdownLines,
        ];
    }

}
