<?php

namespace App\Observers;

use App\Enums\GlobalLog\LogActionBooking;
use App\Models\Passenger;
use App\Support\GlobalLogger;

class PassengerObserver
{
    public function updated(Passenger $passenger): void
    {
        $emptySeatBefore = $passenger->getOriginal('empty_seat');
        $emptySeatAfter = $passenger->empty_seat;

        if (!$emptySeatBefore && $emptySeatAfter) {
            GlobalLogger::log(
                LogActionBooking::SET_EMPTY_SEAT,
                'booking',
                $passenger->booking_id,
                'Set empty seat',
                [
                    'before' => [
                        'empty_seat' => $emptySeatBefore,
                    ],
                    'after' => [
                        'empty_seat' => $emptySeatAfter,
                        'passenger_id' => $passenger->id,
                        'passenger_order' => $passenger->passenger_order,
                        'lead_passenger' => $passenger->lead_passenger,
                        'survivor_number' => $passenger->survivor_number,
                        'booking_id' => $passenger->booking_id,
                        'booking_request_id' => $passenger->booking->booking_request_id,
                    ],
                ]
            );
        } elseif ($emptySeatBefore && !$emptySeatAfter) {
            GlobalLogger::log(
                LogActionBooking::UNSET_EMPTY_SEAT,
                'booking',
                $passenger->booking_id,
                'Unset empty seat',
                [
                    'before' => [
                        'empty_seat' => $emptySeatBefore,
                    ],
                    'after' => [
                        'empty_seat' => $emptySeatAfter,
                        'passenger_id' => $passenger->id,
                        'passenger_order' => $passenger->passenger_order,
                        'lead_passenger' => $passenger->lead_passenger,
                        'survivor_number' => $passenger->survivor_number,
                        'booking_id' => $passenger->booking_id,
                        'booking_request_id' => $passenger->booking->booking_request_id,
                    ],
                ]
            );
        } elseif ($passenger->first_name === null && $passenger->getOriginal("first_name") !== null) {
            //Released seat
            GlobalLogger::log(
                LogActionBooking::PASSENGER_RELEASED,
                'booking',
                $passenger->booking_id,
                'Passenger updated',
                [
                    'before' => [
                        'passenger_allocated_cost' => $passenger->getOriginal('passenger_allocated_cost'),
                        'passenger_balance' => $passenger->getOriginal('passenger_balance'),
                        'empty_seat' => $emptySeatBefore,
                        'passenger_id' => $passenger->getOriginal('id'),
                        'passenger_order' => $passenger->getOriginal('passenger_order'),
                        'lead_passenger' => $passenger->getOriginal('lead_passenger'),
                        'survivor_number' => $passenger->getOriginal('survivor_number'),
                        'payment_method' => $passenger->getOriginal('payment_method'),
                        'booking_id' => $passenger->booking_id,
                        'booking_request_id' => $passenger->booking->booking_request_id,
                    ],
                ]
            );
        } else {
            if ($passenger->getOriginal('passenger_allocated_cost') === 0 || $passenger->getOriginal('passenger_allocated_cost') === "0.00") {
                $description = 'Passenger created';
                $action = LogActionBooking::PASSENGER_CREATED;
            } elseif ($passenger->getOriginal('dob') === null && $passenger->getOriginal('email') === null &&
                $passenger->dob !== null && $passenger->email !== null) {
                $description = 'Passenger added';
                $action = LogActionBooking::PASSENGER_ADDED;
            } else {
                $description = 'Passenger updated';
                $action = LogActionBooking::PASSENGER_UPDATED;
            }

            $changeLog = self::generatePassengerChangeLog($passenger);

            //If only passenger_allocated_cost changed, and it's an update, skip logging
            //As per current agreement we do not log passenger updates if it only comes from syncAllocatedCost
            if (
                $action === LogActionBooking::PASSENGER_UPDATED &&
                empty(array_diff(array_keys($changeLog['before']), ['passenger_allocated_cost']))
            ) {
                return;
            }

            if (trim($changeLog['passenger'])) {
                $description .= ': ' . $changeLog['passenger'];
            } else {
                $description .= '. Passenger order: ' . $passenger->passenger_order;
            }

            GlobalLogger::log(
                $action,
                'booking',
                $passenger->booking_id,
                $description,
                [
                    'before' => $changeLog['before'],
                    'after'  => $changeLog['after'],
                ]
            );
        }
    }

    public static function generatePassengerChangeLog(Passenger $passenger): array
    {
        $fieldsToTrack = [
            'confirmed_booking_email',
            'lead_passenger',
            'survivor_number',
            'payment_method',
            'gender',
            'first_name',
            'middle_name',
            'last_name',
            'dob',
            'citizenship',
            'address_first',
            'address_second',
            'city',
            'state',
            'postal_code',
            'country',
            'email',
            'phone',
            'emergency_c_name',
            'emergency_c_phone',
            'special_request',
            'special_options',
            'hear_about',
            'referral_details',
            'newsletter',
            'travel_info',
            'terms_n_cons',
            'empty_seat',
            'cabin_conf_accp',
            'single_t_agreement',
            'passenger_allocated_cost',
            'passenger_balance',
            'was_on_board',
            'language',
        ];

        $changes = [];
        $before = [];
        $after = [];

        // Always include names for identification
        $changes['passenger'] = sprintf(
            '%s %s',
            $passenger->getOriginal('first_name'),
            $passenger->getOriginal('last_name')
        );

        foreach ($fieldsToTrack as $field) {
            $old = $passenger->getOriginal($field);
            $new = $passenger->$field;

            // Compare JSON fields safely
            if (is_array($old) || is_array($new)) {
                $old = json_encode($old);
                $new = json_encode($new);
            }

            if ($old != $new) {
                $before[$field] = $old;
                $after[$field] = $new;
            }
        }

        $changes['before'] = $before;
        $changes['after'] = $after;

        return $changes;
    }
}
