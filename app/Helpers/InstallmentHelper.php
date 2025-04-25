<?php

namespace App\Helpers;

use App\Models\Booking;
use Illuminate\Support\Carbon;
use function PHPUnit\Framework\isArray;

class InstallmentHelper
{
    public static function getFirstUnpaidInstallmentForBooking(Booking $booking): string|null
    {
        $longestDueDateInstallment = [];

        foreach ($booking->passengers as $passenger) {
            $passengerInstallmentStatus = $passenger->installment_status;
            if (is_array($passengerInstallmentStatus['next_installment'])) {
                $longestDueDateInstallment[] = $passengerInstallmentStatus['next_installment']['due_date'];
            }
        }

        $oldestDate = collect($longestDueDateInstallment)
            ->map(fn($date) => Carbon::parse($date))
            ->sort()
            ->first();

        return $oldestDate?->toDateString();
    }
}
