<?php

namespace App\Observers;

use App\Enums\GlobalLog\LogActionBooking;
use App\Models\OnboardCredit;
use App\Support\GlobalLogger;

class OnboardCreditObserver {
    public function created(OnboardCredit $credit): void {
        $booking = $credit->passenger->booking;

        GlobalLogger::log(LogActionBooking::ONBOARD_CREDIT_CREATED, 'booking', $booking->id, 'Onboard credit created', [
            'after' => [
                'onboard_credit_id' => $credit->id,
                'passenger_id' => $credit->passenger_id,
                'amount' => $credit->amount,
                'reason' => $credit->reason,
                'booking_id' => $booking->id,
                'booking_request_id' => $booking->booking_request_id,
            ],
        ]);
    }

    public function deleted(OnboardCredit $credit): void {
        $booking = $credit->passenger->booking;

        GlobalLogger::log(LogActionBooking::ONBOARD_CREDIT_DELETED, 'booking', $booking->id, 'Onboard credit deleted', [
            'before' => [
                'onboard_credit_id' => $credit->id,
                'passenger_id' => $credit->passenger_id,
                'amount' => $credit->amount,
                'reason' => $credit->reason,
                'booking_id' => $booking->id,
                'booking_request_id' => $booking->booking_request_id,
            ],
        ]);
    }
}
