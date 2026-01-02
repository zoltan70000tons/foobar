<?php

namespace App\Helpers;

use Illuminate\Support\Carbon;

/**
 *  Verifies if a given date of birth meets the specified age restriction.
 *
 * @return bool
 */
class AgeRestriction {
    public static function isAgeValid(string $dateOfBirth, ?int $ageRestriction) {
        if ($ageRestriction === null) {
            return true;
        }

        $dob = Carbon::createFromFormat('Y-m-d', $dateOfBirth);
        $currentDate = Carbon::now();
        $age = $dob->diffInYears($currentDate);

        return $age >= $ageRestriction;
    }
}
