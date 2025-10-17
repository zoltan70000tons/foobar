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
            GlobalLogger::log(
                LogActionBooking::PASSENGER_UPDATED,
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
                    ],
                    'after' => [
                        'passenger_allocated_cost' => $passenger->passenger_allocated_cost,
                        'passenger_balance' => $passenger->passenger_balance,
                        'empty_seat' => $emptySeatAfter,
                        'passenger_id' => $passenger->id,
                        'passenger_order' => $passenger->passenger_order,
                        'lead_passenger' => $passenger->lead_passenger,
                        'survivor_number' => $passenger->survivor_number,
                        'payment_method' => $passenger->payment_method,
                        'booking_id' => $passenger->booking_id,
                        'booking_request_id' => $passenger->booking->booking_request_id,
                    ],
                ]
            );
        }
    }
}
