<?php

namespace App\Services\CabinInventoryIntegrity\Rules\PrivateCabin;

use App\Enums\BookingStatus;
use App\Services\CabinInventoryIntegrity\Rules\IntegrityRule;
use App\Services\CabinInventoryIntegrity\Rules\RuleResult;

/**
 * ----------------------------------------------
 * Private Booked Booking Count Rule
 * ----------------------------------------------
 *
 * description: Private cabin is BOOKED but booking count is not 1.
 * code: private.booked.booking_count
 *
 * ----------------------------------------------
 *
 */
class PrivateBookedBookingCountRule implements IntegrityRule {
    public function check(mixed $context): array {
        if (!$context->isPrivateCabin || $context->status !== 'BOOKED') {
            return [];
        }

        if (!empty(array_intersect($context->bookingStatuses, [BookingStatus::CANCELLED->value]))) {
            return [];
        }

        if ($context->bookingCount === 1) {
            return [];
        }

        return [
            new RuleResult(
                'private.booked.booking_count',
                'Private cabin is BOOKED but booking count is: ' . $context->bookingCount . ' instead of 1.',
                [
                    'bookingCount' => $context->bookingCount,
                    'passengerCount' => $context->passengerCount,
                    'bookingIds' => $context->bookingIds,
                    'bookingStatuses' => $context->bookingStatuses,
                ],
            ),
        ];
    }
}
