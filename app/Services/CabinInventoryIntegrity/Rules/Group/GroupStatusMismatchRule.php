<?php

namespace App\Services\CabinInventoryIntegrity\Rules\Group;

use App\Services\CabinInventoryIntegrity\Rules\IntegrityRule;
use App\Services\CabinInventoryIntegrity\Rules\RuleResult;

/**
 * ----------------------------------------------
 * Group Status Mismatch Rule
 * ----------------------------------------------
 *
 * description: Status should be the same for all cabins in the group.
 * code: group.status.mismatch
 *
 * ----------------------------------------------
 *
 */
class GroupStatusMismatchRule implements IntegrityRule {
    public function check(mixed $context): array {
        $mismatches = [];
        foreach ($context->statusByCabin as $cabinId => $status) {
            if ($status !== $context->referenceStatus) {
                $mismatches[] = $cabinId;
            }
        }

        if (empty($mismatches)) {
            return [];
        }

        return [
            new RuleResult('group.status.mismatch', 'Status should be the same for all cabins in the group.', [
                'group_cabin_ids' => $context->cabins->pluck('id')->all(),
                'reference_status' => $context->referenceStatus,
                'status_mismatches' => array_values($mismatches),
            ]),
        ];
    }
}
