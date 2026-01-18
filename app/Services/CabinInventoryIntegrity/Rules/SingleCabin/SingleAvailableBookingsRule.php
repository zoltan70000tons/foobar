<?php

namespace App\Services\CabinInventoryIntegrity\Rules\SingleCabin;

use App\Services\CabinInventoryIntegrity\Rules\IntegrityRule;
use App\Services\CabinInventoryIntegrity\Rules\RuleResult;

/**
 * ----------------------------------------------
 * Single Available Bookings Rule
 * ----------------------------------------------
 *
 * description: Single ticket cabin is AVAILABLE/RESERVED but has active bookings.
 * code: single.available.bookings
 *
 * ----------------------------------------------
 *
 */
class SingleAvailableBookingsRule implements IntegrityRule {
    public function check(mixed $context): array {
        if (!$context->isSingleCabin || !in_array($context->status, ['AVAILABLE', 'RESERVED'], true)) {
            return [];
        }

        if ($context->bookingCount === 0) {
            return [];
        }

        return [
            new RuleResult(
                'single.available.bookings',
                'Single ticket cabin is AVAILABLE/RESERVED but has active bookings.',
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
