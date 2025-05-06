<?php

namespace App\Services;

use App\Jobs\SendEmailJob;
use App\Models\Booking;
use App\Models\Passenger;
use Blade;
use DB;
use Exception;
use Log;

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
        $values = $this->fetchPlaceholderValues($placeholders, $booking, $passenger, $extraData);
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
    private function fetchPlaceholderValues(array $placeholders, Booking $booking, Passenger $passenger, array $extraData = []): array
    {
        // Booking and passenger details
        $eventLocation = $booking->event->location ?? '';
        $cabinType = $booking->cabinType->cabin_type ?? '';
        $bookingCode = $booking->booking_code ?? '';
        $paymentPlan = $booking->payment_plan ?? '';
        $grandTotal = $booking->getGrandTotal() ?? 0;
        $individualTotal = $passenger->passenger_allocated_cost ?? 0;
      
        // Installment data
        $paymentData = $passenger->installment_status ?? [];
        $nextInstallmentAmountRaw = $paymentData['next_installment']['amount_due'] ?? '';
        $nextInstallmentDateRaw = $paymentData['next_installment']['due_date'] ?? '';
      
        // Adjust totals if on installment plan
        if ($paymentPlan === 'INSTALLMENTS') {
            $paymentCount = $passenger->installments()->where('type', 'PAYMENT')->count();
            if ($paymentCount > 0) {
                $grandTotal /= $paymentCount;
                $individualTotal /= $paymentCount;
            }
        }
      
        // Pre-format values
        $formattedGrandTotal = formatCurrency($grandTotal, true) ?? '';
        $formattedIndividualTotal = formatCurrency($individualTotal, true) ?? '';
        $formattedNextInstallmentAmount = formatCurrency($nextInstallmentAmountRaw) ?? '';
        $formattedNextInstallmentDate = formatDate($nextInstallmentDateRaw) ?? '';
        $passengerName = capitalizeWords($passenger->first_name ?? '');
      
        // Map placeholder values
        $lookup = [
            'EVENT_LOCATION'         => $eventLocation,
            'BOOKING_CODE'           => $bookingCode,
            'PASSENGER_NAME'         => $passengerName,
            'GRAND_TOTAL'            => $formattedGrandTotal,
            'INDIVIDUAL_TOTAL'       => $formattedIndividualTotal,
            'CABIN_TYPE'             => $cabinType,
            'PAYMENT_PLAN'           => $paymentPlan,
            'NEXT_INSTALLMENT_DATE'  => $formattedNextInstallmentDate,
            'NEXT_INSTALLMENT_AMOUNT'=> $formattedNextInstallmentAmount,
        ];
      
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
            Log::info('Booking pdf sendMail: ' . json_encode($bookingPdf));
            Log::info('Event Image sendmail: ' . json_encode($eventImage));
            Log::info('TicketContract sendMail: ' . json_encode($ticketContrac));

            $to = $booking->passengers
                ->pluck('email')
                ->filter(fn($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
                ->values()
                ->toArray();
            Log::info('sending email to: ' . json_encode($to));
            SendEmailJob::dispatch($templateId, $booking, $passenger, $attachments, $extraData, $bookingPdf, $eventImage, $ticketContrac, $emailContent, $subject, $to)
                ->onQueue('emails');
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
}
