<?php

namespace App\Services;

use Carbon\Carbon;

class SurvivorMatchScoring
{
    public static function scoreName(string $a, string $b): float
    {
        $a = strtolower(trim($a));
        $b = strtolower(trim($b));

        similar_text($a, $b, $percent);
        return $percent; // 0-100
    }

    public static function scoreDob($passengerDob, $userDob): float
    {
        if (!$passengerDob || !$userDob) {
            return 0;
        }

        $p = Carbon::parse($passengerDob);
        $u = Carbon::parse($userDob);

        // Perfect match
        if ($p->isSameDay($u)) {
            return 100;
        }

        // Check for US/EU swapped month/day
        // Example: 1990-02-12 vs 1990-12-02
        if ($p->format("Y") === $u->format("Y")
            && $p->format("d") === $u->format("m")
            && $p->format("m") === $u->format("d")) {
            return 90; // close enough to be considered strong match
        }

        // Difference in days
        $diff = abs($p->diffInDays($u));

        // Score decreases with distance
        // 0 days = 100
        // 30 days = ~75
        // 365 days = very low
        if ($diff > 365 * 5) { // >5 years → irrelevant
            return 0;
        }

        return max(0, 100 - ($diff / 2)); // adjustable curve
    }

    public static function totalScore(string $fnA, string $fnB, string $lnA, string $lnB, $dobA, $dobB): float
    {
        // Weighting
        $weights = [
            'first' => 0.35,
            'last'  => 0.35,
            'dob'   => 0.30,
        ];

        $firstScore = self::scoreName($fnA, $fnB);
        $lastScore  = self::scoreName($lnA, $lnB);
        $dobScore   = self::scoreDob($dobA, $dobB);

        return
            $firstScore * $weights['first'] +
            $lastScore  * $weights['last'] +
            $dobScore   * $weights['dob'];
    }
}
