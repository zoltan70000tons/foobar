<?php

namespace App\Rules;

use Closure;
use DB;
use Illuminate\Contracts\Validation\ValidationRule;

class CabinNumberIsAvailable implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
         $exists = DB::table('bookings')
            ->join('cabins', 'bookings.cabin_id', '=', 'cabins.id')
            ->join('cabin_specs', 'cabins.cabin_spec_id', '=', 'cabin_specs.id')
            ->where('cabin_specs.cabin_number', $value)
            ->exists();
        if ($exists) {
            $fail("The :attribute is already booked in this event. Please choose a different number or remove the existing booking first.");
        }

        $cabinNumberExistsInSpecs = DB::table('cabin_specs')
                                      ->where('cabin_number', $value)
                                      ->exists();
        if ($cabinNumberExistsInSpecs) {
            $fail("The :attribute already exists. Please choose a valid cabin number.");
        }
    }
}
