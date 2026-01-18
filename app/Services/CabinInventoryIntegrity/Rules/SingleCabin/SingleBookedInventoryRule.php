<?php

namespace App\Services\CabinInventoryIntegrity\Rules\SingleCabin;

use App\Services\CabinInventoryIntegrity\Rules\IntegrityRule;
use App\Services\CabinInventoryIntegrity\Rules\RuleResult;

/**
 * ----------------------------------------------
 * Single Booked Inventory Rule
 * ----------------------------------------------
 *
 * description: Single ticket cabin is BOOKED but inventory is not 0.
 * code: single.booked.inventory
 *
 * ----------------------------------------------
 *
 */
class SingleBookedInventoryRule implements IntegrityRule {
    public function check(mixed $context): array {
        if (!$context->isSingleCabin || $context->status !== 'BOOKED') {
            return [];
        }

        if ($context->inventory === 0) {
            return [];
        }

        return [
            new RuleResult('single.booked.inventory', 'Single ticket cabin is BOOKED but inventory is not 0.', [
                'inventory' => $context->inventory,
                'bookingCount' => $context->bookingCount,
                'passengerCount' => $context->passengerCount,
                'bookingIds' => $context->bookingIds,
                'bookingStatuses' => $context->bookingStatuses,
            ]),
        ];
    }
}
