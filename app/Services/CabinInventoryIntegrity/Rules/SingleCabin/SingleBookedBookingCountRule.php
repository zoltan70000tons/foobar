<?php

namespace App\Services\CabinInventoryIntegrity\Rules\SingleCabin;

use App\Services\CabinInventoryIntegrity\Rules\IntegrityRule;
use App\Services\CabinInventoryIntegrity\Rules\RuleResult;

/**
 * ----------------------------------------------
 * Single Cabin Booked Booking Count Rule
 * ----------------------------------------------
 *
 * description: Single ticket cabin is BOOKED but booking count does not match capacity.
 * code: single.booked.booking_count
 *
 * ----------------------------------------------
 *
 */
class SingleBookedBookingCountRule implements IntegrityRule {
    public function check(mixed $context): array {
        if (!$context->isSingleCabin || $context->status !== 'BOOKED') {
            return [];
        }

        if ($context->bookingCount === $context->capacity) {
            return [];
        }

        return [
            new RuleResult(
                'single.booked.booking_count',
                'Single ticket cabin is BOOKED but booking count does not match capacity.',
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
