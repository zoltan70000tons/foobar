<?php

namespace App\Services\CabinInventoryIntegrity\Rules\SingleCabin;

use App\Services\CabinInventoryIntegrity\Rules\IntegrityRule;
use App\Services\CabinInventoryIntegrity\Rules\RuleResult;

/**
 * ----------------------------------------------
 * Single Passenger Entries Rule
 * ----------------------------------------------
 *
 * description: Single ticket cabin is BOOKED but passenger entries do not match current inventory logic (1 pax per ticket sold).
 * code: single.booked.passenger_entries
 *
 * ----------------------------------------------
 *
 */
class SinglePassengerEntriesRule implements IntegrityRule {
    public function check(mixed $context): array {
        if (!$context->isSingleCabin || $context->status !== 'BOOKED') {
            return [];
        }

        if ($context->passengerCount + $context->inventory === $context->capacity) {
            return [];
        }

        return [
            new RuleResult(
                'general.single.one.entries',
                'For single ticket cabins, passenger entries must match current inventory logic (1 pax per ticket sold).',
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
