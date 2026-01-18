<?php

namespace App\Services\CabinInventoryIntegrity\Rules\SingleCabin;

use App\Services\CabinInventoryIntegrity\Rules\IntegrityRule;
use App\Services\CabinInventoryIntegrity\Rules\RuleResult;

/**
 * ----------------------------------------------
 * Single Partially Booked Passengers Rule
 * ----------------------------------------------
 *
 * description: Single ticket cabin is PARTIALLY_BOOKED but passenger count does not match booking count.
 * code: single.partially_booked.passengers
 *
 * ----------------------------------------------
 *
 */
class SinglePartiallyBookedPassengersRule implements IntegrityRule {
    public function check(mixed $context): array {
        if (!$context->isSingleCabin || $context->status !== 'PARTIALLY_BOOKED') {
            return [];
        }

        if ($context->passengerCount === $context->bookingCount) {
            return [];
        }

        return [
            new RuleResult('single.partially_booked.passengers', 'Passenger count does not match booking count.', [
                'bookingCount' => $context->bookingCount,
                'passengerCount' => $context->passengerCount,
                'bookingIds' => $context->bookingIds,
                'bookingStatuses' => $context->bookingStatuses,
            ]),
        ];
    }
}
