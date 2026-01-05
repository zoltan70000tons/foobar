<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidLogAction implements ValidationRule {
    public function __construct(private string $type) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void {
        $allowed = config("log_actions.{$this->type}", []);
        if (!in_array($value, $allowed, true)) {
            $fail("Invalid action '$value' for type '{$this->type}'.");
        }
    }
}
