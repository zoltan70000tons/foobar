<?php

namespace App\Services\CabinInventoryIntegrity\Rules\Group;

use App\Services\CabinInventoryIntegrity\Rules\IntegrityRule;
use App\Services\CabinInventoryIntegrity\Rules\RuleResult;

/**
 * ----------------------------------------------
 * Group Inventory Mismatch Rule
 * ----------------------------------------------
 *
 * description: Inventory should be the same for all cabins in the group.
 * code: group.inventory.mismatch
 *
 * ----------------------------------------------
 *
 */
class GroupInventoryMismatchRule implements IntegrityRule {
    public function check(mixed $context): array {
        $mismatches = [];
        foreach ($context->inventoryByCabin as $cabinId => $inventory) {
            if ($inventory !== $context->referenceInventory) {
                $mismatches[] = $cabinId;
            }
        }

        if (empty($mismatches)) {
            return [];
        }

        return [
            new RuleResult('group.inventory.mismatch', 'Inventory should be the same for all cabins in the group.', [
                'group_cabin_ids' => $context->cabins->pluck('id')->all(),
                'reference_inventory' => $context->referenceInventory,
                'inventory_mismatches' => array_values($mismatches),
            ]),
        ];
    }
}
