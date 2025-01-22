<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class NoForbiddenCharacters implements ValidationRule
{
    /**
     * List of forbidden characters.
     *
     * @var array
     */
    protected $forbiddenCharacters = [
        "'", '"', ';', '<', '>', '\\', '/', '`', '--', '#', '%', '*', '=', '(', ')'
    ];

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  Closure(string): PotentiallyTranslatedString  $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        foreach ($this->forbiddenCharacters as $char) {
            if (strpos($value, $char) !== false) {
                $fail("The :attribute field contains forbidden characters: $char.");
                return;
            }
        }
    }
}
