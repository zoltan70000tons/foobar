<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait StringNormalization
{
  /**
   * Check if two strings are similar based on Levenshtein distance or soundex.
  |-------------------------------------------------------------------|
  | Input Name   | Stored Name | Match? | Why?                        |
  |--------------|------------|--------|------------------------------|
  | José         | JOSE       | ✅     | Special character removed    |
  | O’Connor     | OCONNOR    | ✅     | Apostrophe removed           |
  | MacDonald    | MCDONALD   | ✅     | Similar pronunciation        |
  | John         | Jon        | ✅     | Levenshtein distance = 1     |
  | Marry        | Mary       | ❌     | Too different (distance = 3) |
  */
  /**
   * Normalize a string by removing special characters and converting to uppercase.
   */
  public function normalizeString(string|null $string): string
  {
    if (!$string) {
      return ''; // Return empty string if input is null or empty
    }

    return strtoupper(trim(Str::ascii($string))); // Remove accents and normalize casing
  }

  /**
   * Check if two strings are similar based on Levenshtein distance and Soundex.
   */
  public function isSimilar(string $input, string $stored): bool
  {
    // Direct match
    if ($input === $stored) {
      return true;
    }

    // Check Soundex (similar pronunciation)
    if (soundex($input) === soundex($stored)) {
      return true;
    }

    // Allow minor typos with Levenshtein distance (threshold: 2)
    return levenshtein($input, $stored) <= 2;
  }
}
