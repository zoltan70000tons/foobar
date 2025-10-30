<?php

namespace App\Helpers;

class GlobalLogRemoveUnchanged
{
    public static function removeUnchanged(array $before, array $after): array
    {
        $filteredBefore = [];
        $filteredAfter  = [];

        // Go through every key in either array
        foreach (array_unique(array_merge(array_keys($before), array_keys($after))) as $key) {
            $beforeVal = $before[$key] ?? null;
            $afterVal  = $after[$key] ?? null;

            if (is_array($beforeVal) && is_array($afterVal)) {
                // Recurse into nested arrays
                [$subBefore, $subAfter] = self::removeUnchanged($beforeVal, $afterVal);

                // Only keep if something actually changed inside
                if (!empty($subBefore) || !empty($subAfter)) {
                    $filteredBefore[$key] = $subBefore;
                    $filteredAfter[$key]  = $subAfter;
                }
            } elseif ($beforeVal !== $afterVal) {
                // Only record when value differs
                $filteredBefore[$key] = $beforeVal;
                $filteredAfter[$key]  = $afterVal;
            }
        }

        return [$filteredBefore, $filteredAfter];
    }

}
