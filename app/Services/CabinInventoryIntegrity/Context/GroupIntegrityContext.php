<?php

namespace App\Services\CabinInventoryIntegrity\Context;

use App\Models\Cabin;
use App\Models\Event;
use Illuminate\Support\Collection;

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
class GroupIntegrityContext {
    public function __construct(
        public Event $event,
        public Collection $cabins,
        public Cabin $referenceCabin,
        public string $referenceStatus,
        public int $referenceInventory,
        public array $statusByCabin,
        public array $inventoryByCabin,
        public array $bookingCountsByCabin,
    ) {
    }
}
