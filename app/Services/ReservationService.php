<?php

namespace App\Services;

use App\Models\TemporaryReservation;

class ReservationService {
    /**
     * Check if a temporary reservation exists and belongs to the user
     *
     * @param int $reservationId The reservation ID to check
     * @param int $userId The user ID that should own the reservation
     * @return bool True if reservation exists, belongs to user, and is not expired
     */
    public function reservationExists($reservationId, $userId) {
        return TemporaryReservation::where('id', $reservationId)
            ->where('user_id', $userId)
            ->where('expires_at', '>', now())
            ->exists();
    }

    /**
     * Release a cabin held by a user
     *
     * @param object $user The authenticated user
     * @return array Response with status and message
     */
    public function releaseCabin($user) {
        $reservation = TemporaryReservation::where('user_id', $user->id)->first();

        if (!$reservation) {
            return ['status' => 404, 'message' => 'No reservation found for user'];
        }

        $reservation->delete();

        return ['success' => true, 'message' => 'Cabin released'];
    }
}
