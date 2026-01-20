<?php

namespace App\Services\CabinInventoryIntegrity\Rules\PrivateCabin;

use App\Services\CabinInventoryIntegrity\Rules\IntegrityRule;
use App\Services\CabinInventoryIntegrity\Rules\RuleResult;

/**
 * ----------------------------------------------
 * Private Booked Passengers Rule
 * ----------------------------------------------
 *
 * description: Private cabin is BOOKED but passenger count does not match capacity.
 * code: private.booked.passengers
 *
 * ----------------------------------------------
 *
 */
class PrivateBookedPassengersRule implements IntegrityRule {
    public function check(mixed $context): array {
        if (!$context->isPrivateCabin || $context->status !== 'BOOKED') {
            return [];
        }

        if ($context->passengerCount === $context->capacity) {
            return [];
        }

        return [
            new RuleResult(
                'private.booked.passengers',
                'Private cabin is BOOKED but passenger count does not match capacity.',
                [
                    'bookingCount' => $context->bookingCount,
                    'passengerCount' => $context->passengerCount,
                    'bookingIds' => $context->bookingIds,
                    'bookingStatuses' => $context->bookingStatuses,
                ],
            ),
        ];
    }
}
