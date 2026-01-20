<?php

namespace App\Services\CabinInventoryIntegrity\Rules\SingleCabin;

use App\Services\CabinInventoryIntegrity\Rules\IntegrityRule;
use App\Services\CabinInventoryIntegrity\Rules\RuleResult;

/**
 * ----------------------------------------------
 * Single Partially Booked Status Rule
 * ----------------------------------------------
 *
 * description: Single ticket cabin is PARTIALLY_BOOKED but booking count is invalid.
 * code: single.partially_booked.status
 *
 * ----------------------------------------------
 *
 */
class SinglePartiallyBookedStatusRule implements IntegrityRule {
    public function check(mixed $context): array {
        if (!$context->isSingleCabin || $context->status !== 'PARTIALLY_BOOKED') {
            return [];
        }

        if ($context->bookingCount !== 0 && $context->bookingCount !== $context->capacity) {
            return [];
        }

        return [
            new RuleResult(
                'single.partially_booked.status',
                'Single ticket cabin is PARTIALLY_BOOKED but booking count is invalid.',
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
