<?php

namespace App\Helpers;

use App\Http\Requests\CustomerRequest;
use App\Models\Passenger;
use App\Models\SurvivorNumber;
use App\Models\User;

class CustomerHelper {
    /**
     * Generate a unique survivor number
     *
     * @return string
     */
    public static function generateSurvivorNumber(): string {
        do {
            // Generate a random 9-digit number
            $survivorNumber = str_pad(mt_rand(1, 999999999), 9, '0', STR_PAD_LEFT);
        } while (SurvivorNumber::where('survivor_number', $survivorNumber)->exists());

        return $survivorNumber;
    }

    public static function syncPassengerData(CustomerRequest $request, User $user): void {
        $survivorNumber = $user->survivorNumber?->survivor_number;

        if (!$survivorNumber) {
            return;
        }

        $bookings = $user
            ->bookings()
            ->whereIn('status', ['NEW', 'ON HOLD'])
            ->get();

        foreach ($bookings as $booking) {
            foreach (
                $booking->passengers->where('survivor_number', $user->survivorNumber->survivor_number)
                as $passenger
            ) {
                $passenger->update($request->all());
            }
        }
    }
}
