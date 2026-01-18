<?php

namespace App\Services\CabinInventoryIntegrity\Rules\SingleCabin;

use App\Services\CabinInventoryIntegrity\Rules\IntegrityRule;
use App\Services\CabinInventoryIntegrity\Rules\RuleResult;

/**
 * ----------------------------------------------
 * Single Partially Booked Inventory Rule
 * ----------------------------------------------
 *
 * description: Single ticket cabin is PARTIALLY_BOOKED but inventory does not match capacity minus bookings.
 * code: single.partially_booked.inventory
 *
 * ----------------------------------------------
 *
 */
class SinglePartiallyBookedInventoryRule implements IntegrityRule {
    public function check(mixed $context): array {
        if (!$context->isSingleCabin || $context->status !== 'PARTIALLY_BOOKED') {
            return [];
        }

        $expectedInventory = $context->capacity - $context->bookingCount;
        if ($context->inventory === $expectedInventory) {
            return [];
        }

        return [
            new RuleResult(
                'single.partially_booked.inventory',
                'Single ticket cabin inventory does not match capacity minus bookings. Number of bookings: ' .
                    $context->bookingCount .
                    ', expected inventory: ' .
                    $expectedInventory .
                    ', actual inventory: ' .
                    $context->inventory .
                    '.',
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
