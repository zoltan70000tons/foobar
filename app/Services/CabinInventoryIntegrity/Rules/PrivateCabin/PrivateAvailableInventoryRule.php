<?php

namespace App\Services\CabinInventoryIntegrity\Rules\PrivateCabin;

use App\Services\CabinInventoryIntegrity\Rules\IntegrityRule;
use App\Services\CabinInventoryIntegrity\Rules\RuleResult;

/**
 * ----------------------------------------------
 * Private Available Inventory Rule
 * ----------------------------------------------
 *
 * description: Private cabin is AVAILABLE or RESERVED but inventory is not 1.
 * code: private.available.inventory
 *
 * ----------------------------------------------
 *
 */
class PrivateAvailableInventoryRule implements IntegrityRule {
    public function check(mixed $context): array {
        if (!$context->isPrivateCabin || !in_array($context->status, ['AVAILABLE', 'RESERVED'], true)) {
            return [];
        }

        if ($context->inventory === 1) {
            return [];
        }

        return [
            new RuleResult(
                'private.available.inventory',
                'Private cabin is AVAILABLE/RESERVED but inventory is not 1.',
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
