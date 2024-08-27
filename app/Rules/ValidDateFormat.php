<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;

class ValidDateFormat implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Define the acceptable date formats
        $formats = ['Y/m/d', 'Y-m-d'];

        $valid = false;

        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);
                if ($date && $date->format($format) === $value) {
                    $valid = true;
                    break;
                }
            } catch (\Exception $e) {
                // Skip the current format if an exception occurs
                continue;
            }
        }

        // If the value doesn't match any format, the validation fails
        if (!$valid) {
            $fail("The :attribute must be a valid date in the format Y/m/d or Y-m-d.");
        }
    }
}
