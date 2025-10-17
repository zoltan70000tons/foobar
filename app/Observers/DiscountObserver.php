<?php

namespace App\Observers;

use App\Enums\GlobalLog\LogActionBooking;
use App\Models\PassengerDiscount;
use App\Support\GlobalLogger;

class DiscountObserver
{
    public function created(PassengerDiscount $discount): void
    {
        $booking = $discount->passenger->booking;

        GlobalLogger::log(
            LogActionBooking::DISCOUNT_CREATED,
            'booking',
            $booking->id,
            'Discount created',
            [
                'after' => [
                    'fee_id' => $discount->id,
                    'passenger_id' => $discount->passenger_id,
                    'amount' => $discount->amount,
                    'type' => $discount->type,
                    'operation' => $discount->operation,
                    'notes' => $discount->notes,
                    'booking_id' => $booking->id,
                    'booking_request_id' => $booking->booking_request_id,
                ],
            ]
        );
    }

    public function deleted(PassengerDiscount $discount): void
    {
        $booking = $discount->passenger->booking;

        GlobalLogger::log(
            LogActionBooking::DISCOUNT_DELETED,
            'booking',
            $booking->id,
            'Discount deleted',
            [
                'before' => [
                    'discount_id' => $discount->id,
                    'passenger_id' => $discount->passenger_id,
                    'amount' => $discount->amount,
                    'type' => $discount->type,
                    'operation' => $discount->operation,
                    'notes' => $discount->notes,
                    'booking_id' => $booking->id,
                    'booking_request_id' => $booking->booking_request_id,
                ],
            ]
        );
    }
}
