<?php

namespace App\Services\CabinInventoryIntegrity\Rules\General;

use App\Services\CabinInventoryIntegrity\Rules\IntegrityRule;
use App\Services\CabinInventoryIntegrity\Rules\RuleResult;

/**
 * ----------------------------------------------
 * Unknown Cabin Type Rule
 * ----------------------------------------------
 *
 * description: Cabin type is not recognized for integrity checks.
 * code: general.cabin.type.unknown
 *
 * ----------------------------------------------
 *
 */
class UnknownCabinTypeRule implements IntegrityRule {
    public function check(mixed $context): array {
        if ($context->isSingleCabin || $context->isPrivateCabin) {
            return [];
        }

        return [
            new RuleResult('general.cabin.type.unknown', 'Cabin type is not recognized for integrity checks.', [
                'booking_count' => $context->bookingCount,
                'passenger_count' => $context->passengerCount,
                'booking_ids' => $context->bookingIds,
                'booking_statuses' => $context->bookingStatuses,
            ]),
        ];
    }
}
