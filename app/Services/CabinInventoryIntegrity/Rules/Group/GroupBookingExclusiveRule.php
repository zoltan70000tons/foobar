<?php

namespace App\Services\CabinInventoryIntegrity\Rules\Group;

use App\Services\CabinInventoryIntegrity\Rules\IntegrityRule;
use App\Services\CabinInventoryIntegrity\Rules\RuleResult;

/**
 * ----------------------------------------------
 * Group Booking Exclusive Rule
 * ----------------------------------------------
 *
 * description: If a cabin in the group has a booking, the other shouldn't.
 * code: group.booking.exclusive
 *
 * ----------------------------------------------
 *
 */
class GroupBookingExclusiveRule implements IntegrityRule {
    public function check(mixed $context): array {
        $cabinsWithBookings = array_keys(array_filter($context->bookingCountsByCabin, fn($count) => $count > 0));

        if (count($cabinsWithBookings) <= 1) {
            return [];
        }

        return [
            new RuleResult('group.booking.exclusive', 'If a cabin in the group has a booking, the other shouldn\'t.', [
                'group_cabin_ids' => $context->cabins->pluck('id')->all(),
                'booking_counts_by_cabin' => $context->bookingCountsByCabin,
                'cabins_with_bookings' => array_values($cabinsWithBookings),
            ]),
        ];
    }
}
