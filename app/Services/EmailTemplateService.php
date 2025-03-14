<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\EmailTemplate;
use App\Models\User;
use App\Models\Event;
use App\Models\Passenger;
use Blade;
use DB;
use Exception;
use Illuminate\Http\UploadedFile;
use Mail;
use Barryvdh\DomPDF\Facade as PDF;

class EmailTemplateService
{
    /**
     * get processed template
     *
     * @param int $eventId
     * @param string $lang
     * @param array $extraData (optional)
     * @return string
     */
    public function getProcessedTemplate(int $bookingId, string $lang = 'en', int $id, Passenger $passenger, array $extraData = []): string
    {
        $booking = Booking::with(['cabin.cabinSpec', 'passengers'])->find($bookingId);

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

        $bodyContent = DB::table('email_templates')->where('id', $id)->value('body');

        $data = [
            'header' => DB::table('email_templates')->where('name', '=', $header_template)->value('body'),
            'footer' => DB::table('email_templates')->where('name', '=', $footer_template)->value('body'),
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
     * @param int $eventId
     * @param array $extraData
     * @return array
     */
    private function fetchPlaceholderValues(array $placeholders, Booking $booking, $passenger, array $extraData = []): array
    {
        $event = $booking->event;
        $cabin = $booking->cabin;
        $leadPassenger = $passenger->lead_passenger;

        $values = [];

        foreach ($placeholders as $placeholder) {
            switch ($placeholder) {
                case 'EVENT_LOCATION':
                    $values[$placeholder] = $event->location ?? '';
                    break;
                case 'BOOKING_CODE':
                    $values[$placeholder] = $booking->booking_code ?? '';
                    break;
                case 'PASSENGER_NAME':
                    $values[$placeholder] = capitalizeWords($passenger?->full_name) ?? '';
                    break;
                case 'GRAND_TOTAL':
                    $values[$placeholder] = formatCurrency($booking->getGrandTotal(), true) ?? '';
                    break;
                case 'INDIVIDUAL_TOTAL':
                    $values[$placeholder] = formatCurrency($passenger->passenger_allocated_cost, true) ?? '';
                    break;
                case 'CABIN_TYPE':
                    $values[$placeholder] = $booking->cabinType->cabin_type ?? '';
                    break;
                case 'PAYMENT_PLAN':
                    $values[$placeholder] = $booking->payment_plan ?? '';
                    break;
                default:
                    $values[$placeholder] = $extraData[$placeholder] ?? '';
                    break;
            }
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


    /**
     * Send an email with or without attachments.
     * 
     * @param int $templateId        Email template id
     * @param Booking $booking       Booking instance
     * @param array|null $attachments List of attachments (optional)
     * @param bool $bookingPdf       If true, attach booking confirmation PDF
     * @param bool $eventImage       If true, attach event image
     * @return bool Returns true if all emails were sent successfully, false if any failed
     */
    public function sendEmail(int $templateId, Booking $booking, array $attachments = [], bool $bookingPdf = false, bool $eventImage = false): bool
    {
        $allEmailsSent = true; // Variable to track the success of all emails

        foreach ($booking->passengers as $passenger) {
            $to = $passenger->email;
            $subject = 'Booking Confirmation';
            $subject = DB::table('email_templates')->where('id', $templateId)->value('subject');
            $content = $this->getProcessedTemplate($booking->id, 'en', $templateId, $passenger);

            try {
                // Attach the booking confirmation PDF if required
                if ($bookingPdf) {
                    $pdfService = new PDFService();
                    $pdf = $pdfService->generateBookingConfirmationPDF($booking);

                    if ($pdf instanceof \Barryvdh\DomPDF\PDF) {
                        // Save temporary PDF file
                        $pdfPath = storage_path('app/temp_booking_' . $booking->id . '.pdf');
                        $pdf->save($pdfPath);

                        if (file_exists($pdfPath)) {
                            $attachments[] = [
                                'path' => $pdfPath,
                                'name' => $booking->booking_code . '.pdf',
                                'mime' => 'application/pdf'
                            ];
                        }
                    } else {
                        \Log::warning("PDFService did not return a valid PDF object for Booking ID: " . $booking->id);
                    }
                }

                // 📌 Attach the event image if required
                if ($eventImage) {
                    $eventImageUrl = $booking->event->image;

                    if (filter_var($eventImageUrl, FILTER_VALIDATE_URL)) {
                        $imageData = @file_get_contents($eventImageUrl);
                        if ($imageData !== false) {
                            $mimeType = get_headers($eventImageUrl, 1)["Content-Type"] ?? 'image/jpeg';
                            $attachments[] = [
                                'data' => $imageData,
                                'name' => basename($eventImageUrl),
                                'mime' => $mimeType,
                            ];
                        } else {
                            \Log::warning("Failed to retrieve event image for Booking ID: " . $booking->id);
                        }
                    } else {
                        \Log::warning("Invalid event image URL for Booking ID: " . $booking->id);
                    }
                }

                // 📌 Send the email
                Mail::send([], [], function ($message) use ($to, $subject, $content, $attachments) {
                    $message->to($to)
                        ->subject($subject)
                        ->html($content);

                    foreach ($attachments as $file) {
                        if (isset($file['path'])) {
                            // Attach PDF file from the file system
                            $message->attach($file['path'], [
                                'as' => $file['name'],
                                'mime' => $file['mime']
                            ]);
                        } elseif (isset($file['data'])) {
                            // Attach event image from raw data
                            $message->attachData($file['data'], $file['name'], ['mime' => $file['mime']]);
                        } elseif ($file instanceof UploadedFile) {
                            // Attach file from request
                            $message->attachData(
                                file_get_contents($file->getRealPath()),
                                $file->getClientOriginalName(),
                                ['mime' => $file->getMimeType()]
                            );
                        }
                    }
                });

                // 📌 Delete the temporary PDF file if it was created
                if (isset($pdfPath) && file_exists($pdfPath)) {
                    unlink($pdfPath);
                }
            } catch (Exception $e) {
                \Log::error("Error sending email to {$to}: " . $e->getMessage());
                $allEmailsSent = false; // Mark as false if any email fails
            }
        }

        return $allEmailsSent; // Return true if all emails were sent, false otherwise
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
                'thanks_payment_inst'  => 60,
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
