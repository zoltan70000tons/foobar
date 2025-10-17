<?php

namespace App\Observers;

use App\Enums\GlobalLog\LogActionBooking;
use App\Models\Fee;
use App\Support\GlobalLogger;

class FeeObserver
{
    public function created(Fee $fee): void
    {
        $booking = $fee->passenger->booking;

        GlobalLogger::log(
            LogActionBooking::FEE_CREATED,
            'booking',
            $booking->id,
            'Fee created',
            [
                'after' => [
                    'fee_id' => $fee->id,
                    'passenger_id' => $fee->passenger_id,
                    'amount' => $fee->amount,
                    'type' => $fee->type,
                    'notes' => $fee->notes,
                    'booking_id' => $booking->id,
                    'booking_request_id' => $booking->booking_request_id,
                ],
            ]
        );
    }

    public function deleted(Fee $fee): void
    {
        $booking = $fee->passenger->booking;

        GlobalLogger::log(
            LogActionBooking::FEE_DELETED,
            'booking',
            $booking->id,
            'Fee deleted',
            [
                'before' => [
                    'fee_id' => $fee->id,
                    'passenger_id' => $fee->passenger_id,
                    'amount' => $fee->amount,
                    'type' => $fee->type,
                    'notes' => $fee->notes,
                    'booking_id' => $booking->id,
                    'booking_request_id' => $booking->booking_request_id,
                ],
            ]
        );
    }
}
