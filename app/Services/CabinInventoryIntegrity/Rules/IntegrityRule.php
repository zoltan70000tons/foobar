<?php

namespace App\Services\CabinInventoryIntegrity\Rules;

interface IntegrityRule {
    /**
     * Perform integrity check based on the provided context.
     *
     * @param mixed $context The context containing cabin inventory data.
     * @return array An array of integrity issues found, empty if none.
     */
    public function check(mixed $context): array;
}
