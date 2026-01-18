<?php

namespace App\Services\CabinInventoryIntegrity\Rules\SingleCabin;

use App\Services\CabinInventoryIntegrity\Rules\IntegrityRule;
use App\Services\CabinInventoryIntegrity\Rules\RuleResult;

/**
 * ----------------------------------------------
 * Single Booked Passengers Rule
 * ----------------------------------------------
 *
 * description: Single ticket cabin is BOOKED but passenger count does not match capacity.
 * code: single.booked.passengers
 *
 * ----------------------------------------------
 *
 */
class SingleBookedPassengersRule implements IntegrityRule {
    public function check(mixed $context): array {
        if (!$context->isSingleCabin || $context->status !== 'BOOKED') {
            return [];
        }

        if ($context->passengerCount === $context->capacity) {
            return [];
        }

        return [
            new RuleResult(
                'single.booked.passengers',
                'Single ticket cabin is BOOKED but passenger count does not match capacity.',
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
