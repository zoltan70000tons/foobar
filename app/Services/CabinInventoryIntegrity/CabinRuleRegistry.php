<?php

namespace App\Services\CabinInventoryIntegrity;

use App\Services\CabinInventoryIntegrity\Rules\General\ClosedCabinHasBookingsRule;
use App\Services\CabinInventoryIntegrity\Rules\General\InventoryGreaterThanCapacityRule;
use App\Services\CabinInventoryIntegrity\Rules\General\InventoryNegativeRule;
use App\Services\CabinInventoryIntegrity\Rules\General\UnknownCabinTypeRule;
use App\Services\CabinInventoryIntegrity\Rules\Group\GroupBookingExclusiveRule;
use App\Services\CabinInventoryIntegrity\Rules\Group\GroupInventoryMismatchRule;
use App\Services\CabinInventoryIntegrity\Rules\Group\GroupStatusMismatchRule;
use App\Services\CabinInventoryIntegrity\Rules\PrivateCabin\PrivateAvailableBookingsRule;
use App\Services\CabinInventoryIntegrity\Rules\PrivateCabin\PrivateAvailableInventoryRule;
use App\Services\CabinInventoryIntegrity\Rules\PrivateCabin\PrivateBookedBookingCountRule;
use App\Services\CabinInventoryIntegrity\Rules\PrivateCabin\PrivateBookedInventoryRule;
use App\Services\CabinInventoryIntegrity\Rules\PrivateCabin\PrivateBookedPassengersRule;
use App\Services\CabinInventoryIntegrity\Rules\PrivateCabin\PrivateStatusPartiallyBookedRule;
use App\Services\CabinInventoryIntegrity\Rules\SingleCabin\SingleAvailableBookingsRule;
use App\Services\CabinInventoryIntegrity\Rules\SingleCabin\SingleAvailableInventoryRule;
use App\Services\CabinInventoryIntegrity\Rules\SingleCabin\SingleBookedBookingCountRule;
use App\Services\CabinInventoryIntegrity\Rules\SingleCabin\SingleBookedInventoryRule;
use App\Services\CabinInventoryIntegrity\Rules\SingleCabin\SingleBookedPassengersRule;
use App\Services\CabinInventoryIntegrity\Rules\SingleCabin\SinglePartiallyBookedInventoryRule;
use App\Services\CabinInventoryIntegrity\Rules\SingleCabin\SinglePartiallyBookedPassengersRule;
use App\Services\CabinInventoryIntegrity\Rules\SingleCabin\SinglePartiallyBookedStatusRule;
use App\Services\CabinInventoryIntegrity\Rules\SingleCabin\SinglePassengerEntriesRule;

/**
 * ----------------------------------------------
 * Cabin Inventory Integrity Service - Rule Registry
 * ----------------------------------------------
 *
 * RULES:
 *
 *  private.status.partially_booked - Private cabin status must never be PARTIALLY_BOOKED. ( this is not sinlge ticket)
 *  private.available.bookings - Private cabin is AVAILABLE/RESERVED but has active bookings.
 *  private.available.inventory - Private cabin is AVAILABLE/RESERVED but inventory is not 1.
 *  private.booked.booking_count - Private cabin is BOOKED but booking count is not 1.
 *  private.booked.inventory - Private cabin is BOOKED but inventory is not 0.
 *  private.booked.passengers - Private cabin is BOOKED but passenger count does not match capacity.
 *
 *  single.available.bookings - Single ticket cabin is AVAILABLE/RESERVED but has active bookings.
 *  single.available.inventory - Single ticket cabin is AVAILABLE/RESERVED but inventory does not equal capacity.
 *  single.booked.booking_count - Single ticket cabin is BOOKED but booking count does not match capacity.
 *  single.booked.inventory - Single ticket cabin is BOOKED but inventory is not 0.
 *  single.booked.passengers - Single ticket cabin is BOOKED but passenger count does not match capacity.
 *  single.partially_booked.status - Single ticket cabin is PARTIALLY_BOOKED but booking count is invalid.
 *  single.partially_booked.inventory - Single ticket cabin inventory does not match capacity
 *   minus bookings.
 *  single.partially_booked.passengers - Passenger count does not match booking count.
 *
 *  general.inventory.negative - Cabin inventory is negative.
 *  general.inventory.greater - Cabin inventory is greater than cabin category capacity.
 *  general.single.one.entries - For single ticket, passenger entries must match current invoentory logic. 1 pax per ticket sold.
 *  general.cabins.closed - Cabins with status CLOSED must not have any bookings linked.
 *  general.cabin.type.unknown - Cabin type is not recognized for integrity checks.
 *
 *  group.booking.exclusive - If a cabin in the group has a booking, the other shouldn't.
 *  group.status.mismatch - Status should be the same for all cabins in the group.
 *  group.inventory.mismatch - Inventory should be the same for all cabins in the group.
 *
 * ----------------------------------------------
 *
 */

final class CabinRuleRegistry {
    /**
     * @return array<int, object>
     */
    public function general(): array {
        return [new InventoryNegativeRule(), new InventoryGreaterThanCapacityRule(), new ClosedCabinHasBookingsRule()];
    }

    /** @return array<int, object> */
    public function single(): array {
        return [
            new SingleAvailableBookingsRule(),
            new SingleAvailableInventoryRule(),
            new SingleBookedBookingCountRule(),
            new SingleBookedInventoryRule(),
            new SingleBookedPassengersRule(),
            new SinglePassengerEntriesRule(),
            new SinglePartiallyBookedStatusRule(),
            new SinglePartiallyBookedInventoryRule(),
            new SinglePartiallyBookedPassengersRule(),
        ];
    }

    /** @return array<int, object> */
    public function private(): array {
        return [
            new PrivateStatusPartiallyBookedRule(),
            new PrivateAvailableBookingsRule(),
            new PrivateAvailableInventoryRule(),
            new PrivateBookedBookingCountRule(),
            new PrivateBookedInventoryRule(),
            new PrivateBookedPassengersRule(),
        ];
    }

    /** @return array<int, object> */
    public function group(): array {
        return [new GroupBookingExclusiveRule(), new GroupStatusMismatchRule(), new GroupInventoryMismatchRule()];
    }

    public function unknownCabinType(): UnknownCabinTypeRule {
        return new UnknownCabinTypeRule();
    }
}
