<?php

namespace App\Services\CabinInventoryIntegrity\Rules\General;

use App\Services\CabinInventoryIntegrity\Rules\IntegrityRule;
use App\Services\CabinInventoryIntegrity\Rules\RuleResult;

/**
 * ----------------------------------------------
 * Closed Cabin Has Bookings Rule
 * ----------------------------------------------
 *
 * description: Cabins with status CLOSED must not have any bookings linked.
 * code: general.cabins.closed
 *
 * ----------------------------------------------
 *
 */
class ClosedCabinHasBookingsRule implements IntegrityRule {
    public function check(mixed $context): array {
        if ($context->status !== 'CLOSED' || $context->bookingCount === 0) {
            return [];
        }

        return [
            new RuleResult('general.cabins.closed', 'Cabins with status CLOSED must not have any bookings linked.', [
                'bookingCount' => $context->bookingCount,
                'passengerCount' => $context->passengerCount,
                'bookingIds' => $context->bookingIds,
                'bookingStatuses' => $context->bookingStatuses,
            ]),
        ];
    }
}
