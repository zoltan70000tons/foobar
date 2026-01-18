<?php

namespace App\Services\CabinInventoryIntegrity\Rules\General;

use App\Services\CabinInventoryIntegrity\Rules\IntegrityRule;
use App\Services\CabinInventoryIntegrity\Rules\RuleResult;

/**
 * ----------------------------------------------
 * Inventory Greater Than Capacity Rule
 * ----------------------------------------------
 *
 * description: Cabin inventory is greater than cabin category capacity.
 * code: general.inventory.greater
 *
 * ----------------------------------------------
 *
 */
class InventoryGreaterThanCapacityRule implements IntegrityRule {
    public function check(mixed $context): array {
        if ($context->inventory <= $context->capacity) {
            return [];
        }

        return [
            new RuleResult('general.inventory.greater', 'Cabin inventory is greater than cabin category capacity.', [
                'inventory' => $context->inventory,
                'capacity' => $context->capacity,
                'bookingCount' => $context->bookingCount,
                'passengerCount' => $context->passengerCount,
                'bookingIds' => $context->bookingIds,
                'bookingStatuses' => $context->bookingStatuses,
            ]),
        ];
    }
}
