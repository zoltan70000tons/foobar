<?php

namespace App\Helpers;

use App\Models\Booking;

class InstallmentHelper
{
    public static function getFirstUnpaidInstallmentForBooking(Booking $booking): string|null
    {
        $longestDueDateInstallment = null;

        foreach ($booking->passengers as $passenger) {
            $installments = $passenger->installments;

            foreach ($installments as $installment) {
                $payment = \DB::table('installment_payment')
                    ->where('installment_id', $installment->id)
                    ->where('status', 'PAID')
                    ->first();

                if (!$payment) {
                    if (!$longestDueDateInstallment || $installment->due_date < $longestDueDateInstallment->due_date) {
                        $longestDueDateInstallment = $installment;
                    }
                }
            }
        }

        return $longestDueDateInstallment?->due_date;
    }
}
