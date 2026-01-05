<?php

namespace App\Observers;

use App\Enums\GlobalLog\LogActionBooking;
use App\Models\BookingAgentSessions;
use App\Support\GlobalLogger;

class BookingAgentSessionsObserver {
    public function created(BookingAgentSessions $bookingAgentSessions): void {
        GlobalLogger::log(
            LogActionBooking::AGENT_SESSION_CREATED,
            'booking',
            $bookingAgentSessions->booking_id,
            'Booking is opened for edit',
            [
                'after' => [
                    'booking_id' => $bookingAgentSessions->booking_id,
                    'booking_agent_sessions_id' => $bookingAgentSessions->id,
                ],
            ],
        );
    }

    public function updated(BookingAgentSessions $bookingAgentSessions): void {
        GlobalLogger::log(
            LogActionBooking::AGENT_SESSION_UPDATED,
            'booking',
            $bookingAgentSessions->booking_id,
            'Booking is opened for edit',
            [
                'after' => [
                    'booking_id' => $bookingAgentSessions->booking_id,
                    'booking_agent_sessions_id' => $bookingAgentSessions->id,
                ],
            ],
        );
    }

    public function deleted(BookingAgentSessions $bookingAgentSessions): void {
        GlobalLogger::log(
            LogActionBooking::AGENT_SESSION_DELETED,
            'booking',
            $bookingAgentSessions->booking_id,
            'Booking is no longer being edited',
            [
                'before' => [
                    'booking_id' => $bookingAgentSessions->booking_id,
                    'booking_agent_sessions_id' => $bookingAgentSessions->id,
                ],
            ],
        );
    }
}
