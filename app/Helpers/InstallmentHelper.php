<?php

namespace App\Helpers;

use App\Models\Booking;
use Illuminate\Support\Carbon;
use function PHPUnit\Framework\isArray;

class InstallmentHelper {
    public static function getFirstUnpaidInstallmentForBooking(Booking $booking): string|null {
        $longestDueDateInstallment = [];

        foreach ($booking->passengers as $passenger) {
            $passengerInstallmentStatus = $passenger->installment_status;
            if (is_array($passengerInstallmentStatus['next_installment'])) {
                $longestDueDateInstallment[] = $passengerInstallmentStatus['next_installment']['due_date'];
            }
        }

        $oldestDate = collect($longestDueDateInstallment)->map(fn($date) => Carbon::parse($date))->sort()->first();

        return $oldestDate?->toDateString();
    }

    /**
     * Calculate the maximum number of monthly installments that can be created
     * for a booking (or for a specific passenger) so that the last installment
     * is no later than one week before the event start date.
     *
     * Returns an integer between 0 and 5.
     * If a passenger id is provided and that passenger has existing PAYMENT
     * installments, the base date will be the first installment due_date.
     * Otherwise the base date will be booking.created_at.
     */
    public static function maxInstallmentsAllowed(Booking $booking, ?int $passengerId = null): int {
        $baseDate = Carbon::parse($booking->created_at);

        if ($passengerId) {
            $passenger = $booking->passengers->firstWhere('id', $passengerId);
            if ($passenger) {
                $first = $passenger->installments()->where('type', 'PAYMENT')->orderBy('due_date')->first();
                if ($first && $first->due_date) {
                    $baseDate = Carbon::parse($first->due_date);
                }
            }
        }

        if (!$booking->event || !$booking->event->start_date) {
            return 0;
        }

        $lastAllowed = Carbon::parse($booking->event->start_date)->subWeek();

        $allowedMax = 0;
        for ($i = 0; $i < 5; $i++) {
            $due = $baseDate->copy()->addMonths($i);
            if ($due->lte($lastAllowed)) {
                $allowedMax = $i + 1;
            } else {
                break;
            }
        }

        return $allowedMax;
    }
}
