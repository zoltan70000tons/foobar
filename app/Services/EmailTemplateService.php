<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\EmailTemplate;
use App\Models\User;
use App\Models\Event;
use Blade;
use DB;

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
    public function getProcessedTemplate(int $bookingId, string $lang = 'en', int $id, array $extraData = []): string
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
        $values = $this->fetchPlaceholderValues($placeholders, $booking, $extraData);
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
    private function fetchPlaceholderValues(array $placeholders, Booking $booking, array $extraData = []): array
    {
        $event = $booking->event;
        $cabin = $booking->cabin;
        $passengers = $booking->passengers;
        $leadPassenger = $passengers->firstWhere('lead_passenger', true);

        $values = [];

        foreach ($placeholders as $placeholder) {
            switch ($placeholder) {
                case 'EVENT_LOCATION':
                    $values[$placeholder] = $event->location ?? '';
                    break;
                case 'BOOKING_CODE':
                    $values[$placeholder] = $booking->booking_code ?? '';
                    break;
                case 'LEAD_PASSENGER':
                    $values[$placeholder] = capitalizeWords($leadPassenger?->full_name) ?? '';
                    break;
                case 'GRAND_TOTAL':
                    $values[$placeholder] = formatCurrency($booking->getGrandTotal(), true) ?? '';
                    break;
                case 'INDIVIDUAL_TOTAL':
                    $values[$placeholder] = formatCurrency($booking->cabin->category->price, true) ?? '';
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
}
