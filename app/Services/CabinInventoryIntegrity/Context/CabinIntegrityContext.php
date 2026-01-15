<?php

namespace App\Services\CabinInventoryIntegrity\Context;

use App\Models\Cabin;
use App\Models\Event;

/**
 * ----------------------------------------------
 * Cabin Inventory Integrity Service - Context
 * ----------------------------------------------
 *
 * This context class holds all relevant data about a cabin's inventory state
 * needed for integrity checks. It encapsulates properties such as the cabin's
 * current status, inventory levels, booking counts, passenger counts, and
 * associated booking details.
 * ----------------------------------------------
 *
 */
class CabinIntegrityContext {
    public function __construct(
        public Event $event,
        public Cabin $cabin,
        public int $capacity,
        public string $status,
        public int $inventory,
        public int $bookingCount,
        public int $passengerCount,
        public array $bookingIds,
        public array $bookingStatuses,
        public bool $isSingleCabin,
        public bool $isPrivateCabin,
    ) {
    }
}
