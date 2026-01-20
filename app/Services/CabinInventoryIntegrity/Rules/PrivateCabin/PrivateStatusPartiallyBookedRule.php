<?php

namespace App\Services\CabinInventoryIntegrity\Rules\PrivateCabin;

use App\Services\CabinInventoryIntegrity\Rules\IntegrityRule;
use App\Services\CabinInventoryIntegrity\Rules\RuleResult;

/**
 * ----------------------------------------------
 * Private Partially Booked Status Rule
 * ----------------------------------------------
 *
 * description: Private cabin status must never be PARTIALLY_BOOKED.
 * code: private.status.partially_booked
 *
 * ----------------------------------------------
 *
 */
class PrivateStatusPartiallyBookedRule implements IntegrityRule {
    public function check(mixed $context): array {
        if (!$context->isPrivateCabin || $context->status !== 'PARTIALLY_BOOKED') {
            return [];
        }

        return [
            new RuleResult('private.status.partially_booked', 'Private cabin status must never be PARTIALLY_BOOKED.', [
                'bookingCount' => $context->bookingCount,
                'passengerCount' => $context->passengerCount,
                'bookingIds' => $context->bookingIds,
                'bookingStatuses' => $context->bookingStatuses,
            ]),
        ];
    }
}
