<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\EmailTemplate;
use App\Models\User;
use App\Models\Event;
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
    public function getProcessedTemplate(int $bookingId, string $lang = 'en', string $name, array $extraData = []): string
    {
        $booking = Booking::find($bookingId);
        if (!$booking) {
            throw new \Exception('Booking not found');
        }
        $eventId = $booking->event_id;
        $template = DB::table('email_templates')->where('event_id', $eventId)->where('lang', $lang)->where('name', $name)->first();

        if (!$template) {
            throw new \Exception('Email template not found');
        }

        // get placeholders from template
        $placeholders = $this->extractPlaceholders($template->body);

        // search for values of placeholders in DB or extra data
        $values = $this->fetchPlaceholderValues($placeholders, $booking, $extraData);

        // replace placeholders in template
        return $this->replacePlaceholders($template->body, $values);
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
    private function fetchPlaceholderValues(array $placeholders, Booking $booking, array $extraData): array
    {
        $values = [];
        $eventId = $booking->event_id;

        // get event data
        $event = Event::find($eventId);
        $cabin = $booking->cabin;
        $passengers = $booking->passengers;
        $leadPassenger = $passengers->firstWhere('lead_passenger', true);
        $remainingPassengers = $passengers
            ->reject(fn($passenger) => $passenger->lead_passenger)
            ->sortBy('id');
        

        if (!$event) {
            throw new \Exception('Event not found');
        }


        foreach ($placeholders as $placeholder) {
            switch ($placeholder) {
                case 'event_name':
                    $values[$placeholder] = $event->name ?? '{{event_name}}';
                    break;
                case 'event_date':
                    $values[$placeholder] = $event->start_date ?? '{event_date}';
                    break;
                case 'event_location':
                    $values[$placeholder] = $event->location ?? '{{event_location}}';
                    break;
                case 'booking_code':
                    $booking->booking_code ?? '{{booking_code}}';
                    break;
                case 'payment_plan':
                    $booking->payment_plan ?? '{payment_plan}';
                    break;
                case 'booking_status':
                    $booking->status ?? '{{booking_status}}';
                    break;
                case 'cabin_status':
                    $cabin->status ?? '{{cabin_status}}';
                    break;
                case 'cabin_number':
                    $cabin->cabinSpec->cabin_number ?? '{{cabin_number}}';
                    break;
                case 'total':
                   // $booking->total ?? '{{total}}';
                    break;
                case 'lead_passenger':
                    $leadPassenger->full_name ?? '{{lead_passenger}}';
                    break;
                case 'lead_passenger_allocated_cost':
                    $leadPassenger->passenger_allocated_cost ?? '{{lead_passenger_allocated_cost}}';
                    break;
                case 'lead_passenger_balance':
                    $leadPassenger->passenger_balance ?? '{{lead_passenger_balance}}';
                    break;
                default:
                    $values[$placeholder] = $extraData[$placeholder] ?? '{{placeholder}}';
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
