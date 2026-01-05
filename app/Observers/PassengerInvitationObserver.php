<?php

namespace App\Observers;

use App\Enums\GlobalLog\LogActionBooking;
use App\Models\PassengerInvitation;
use App\Support\GlobalLogger;

class PassengerInvitationObserver {
    public function deleted(PassengerInvitation $invitation): void {
        $booking = $invitation->passenger->booking;

        GlobalLogger::log(
            LogActionBooking::PASSENGER_INVITATION_DELETED,
            'booking',
            $booking->id,
            'Invitation deleted',
            [
                'before' => [
                    'fee_id' => $invitation->id,
                    'passenger_id' => $invitation->passenger_id,
                    'email' => $invitation->email,
                    'booking_id' => $booking->id,
                    'booking_request_id' => $booking->booking_request_id,
                ],
            ],
        );
    }
}
