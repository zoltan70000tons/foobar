<?php

namespace App\Services\CabinInventoryIntegrity\Rules;

class RuleResult {
    public function __construct(public string $rule, public string $message, public array $context = []) {
    }
}
