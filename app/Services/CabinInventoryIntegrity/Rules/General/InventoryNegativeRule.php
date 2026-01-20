<?php

namespace App\Services\CabinInventoryIntegrity\Rules\General;

use App\Services\CabinInventoryIntegrity\Rules\IntegrityRule;
use App\Services\CabinInventoryIntegrity\Rules\RuleResult;

/**
 * ----------------------------------------------
 * Inventory Negative Rule
 * ----------------------------------------------
 *
 * description: Cabin inventory is negative.
 * code: general.inventory.negative
 *
 * ----------------------------------------------
 *
 */
class InventoryNegativeRule implements IntegrityRule {
    public function check(mixed $context): array {
        if ($context->inventory >= 0) {
            return [];
        }

        return [
            new RuleResult('general.inventory.negative', 'Cabin inventory is negative.', [
                'inventory' => $context->inventory,
                'bookingCount' => $context->bookingCount,
                'passengerCount' => $context->passengerCount,
                'bookingIds' => $context->bookingIds,
                'bookingStatuses' => $context->bookingStatuses,
            ]),
        ];
    }
}
