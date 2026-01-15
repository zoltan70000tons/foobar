<?php

namespace App\Services\CabinInventoryIntegrity\Rules\SingleCabin;

use App\Services\CabinInventoryIntegrity\Rules\IntegrityRule;
use App\Services\CabinInventoryIntegrity\Rules\RuleResult;

/**
 * ----------------------------------------------
 * Single Cabin Available Inventory Rule
 * ----------------------------------------------
 *
 * description: Single ticket cabin is AVAILABLE/RESERVED but inventory does not equal capacity.
 * code: single.available.inventory
 *
 * ----------------------------------------------
 *
 */
class SingleAvailableInventoryRule implements IntegrityRule {
    public function check(mixed $context): array {
        if (!$context->isSingleCabin || !in_array($context->status, ['AVAILABLE', 'RESERVED'], true)) {
            return [];
        }

        if ($context->inventory === $context->capacity) {
            return [];
        }

        return [
            new RuleResult(
                'single.available.inventory',
                'Single ticket cabin is AVAILABLE/RESERVED but inventory does not equal capacity.',
                [
                    'inventory' => $context->inventory,
                    'bookingCount' => $context->bookingCount,
                    'passengerCount' => $context->passengerCount,
                    'bookingIds' => $context->bookingIds,
                    'bookingStatuses' => $context->bookingStatuses,
                ],
            ),
        ];
    }
}
