<?php

namespace App\Services\CabinInventoryIntegrity\Rules\PrivateCabin;

use App\Services\CabinInventoryIntegrity\Rules\IntegrityRule;
use App\Services\CabinInventoryIntegrity\Rules\RuleResult;

/**
 * ----------------------------------------------
 * Private Booked Inventory Rule
 * ----------------------------------------------
 *
 * description: Private cabin is BOOKED but inventory is not 0.
 * code: private.booked.inventory
 *
 * ----------------------------------------------
 *
 */
class PrivateBookedInventoryRule implements IntegrityRule {
    public function check(mixed $context): array {
        if (!$context->isPrivateCabin || $context->status !== 'BOOKED') {
            return [];
        }

        if ($context->inventory === 0) {
            return [];
        }

        return [
            new RuleResult('private.booked.inventory', 'Private cabin is BOOKED but inventory is not 0.', [
                'inventory' => $context->inventory,
                'bookingCount' => $context->bookingCount,
                'passengerCount' => $context->passengerCount,
                'bookingIds' => $context->bookingIds,
                'bookingStatuses' => $context->bookingStatuses,
            ]),
        ];
    }
}
