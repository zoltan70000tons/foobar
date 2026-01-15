<?php

namespace App\Services\CabinInventoryIntegrity\Rules\PrivateCabin;

use App\Services\CabinInventoryIntegrity\Rules\IntegrityRule;
use App\Services\CabinInventoryIntegrity\Rules\RuleResult;

/**
 * ----------------------------------------------
 * Private Available Bookings Rule
 * ----------------------------------------------
 *
 * description: Private cabin is AVAILABLE or RESERVED but has active bookings.
 * code: private.available.bookings
 *
 * ----------------------------------------------
 *
 */
class PrivateAvailableBookingsRule implements IntegrityRule {
    public function check(mixed $context): array {
        if (!$context->isPrivateCabin || !in_array($context->status, ['AVAILABLE', 'RESERVED'], true)) {
            return [];
        }

        if ($context->bookingCount === 0) {
            return [];
        }

        return [
            new RuleResult(
                'private.available.bookings',
                'Private cabin is AVAILABLE/RESERVED but has active bookings.',
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
