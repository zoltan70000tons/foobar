<?php

namespace App\Traits;

use App\Models\BookingLog; // Asegúrate de importar el modelo de log
use Illuminate\Support\Facades\Auth;

trait BookingLogTrait
{
    /**
     * Save a log entry to the database.
     *
     * @param int $bookingId The ID of the booking.
     * @param string $action The action performed (e.g., "Changed booking code").
     * @param string $description The description of the log entry.
     * @param int|null $userId The ID of the user who performed the action (optional, defaults to the authenticated user).
     * @return void
     */
    public function saveBookingLog(int $bookingId, string $action, string $description, ?int $userId = null): void
    {
        BookingLog::create([
            'booking_id' => $bookingId,
            'user_id' => $userId ?? Auth::id(),
            'action' => $action,
            'description' => $description,
        ]);
    }
}
