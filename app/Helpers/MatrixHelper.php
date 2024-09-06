<?php

namespace App\Helpers;

use Illuminate\Support\Str;
use App\Models\Cabin;
use App\Enums\StatusCabin;

class MatrixHelper
{
  /**
   * Truncate the cabin name before the first number using Laravel Str::before().
   *
   * @param string $cabinName
   * @return string
   */
  public static function getNameBeforeFirstNumber(string $cabinName): string
  {
    // ----------------------------------------------------------------
    //
    // Find the first occurrence of a digit and get the part before it
    // 
    //  !!! Probably we will remove it and we will use some table to provide correct names
    //
    // ----------------------------------------------------------------
    for ($i = 0; $i < strlen($cabinName); $i++) {
      if (is_numeric($cabinName[$i])) {
        $tempName = Str::before($cabinName, $cabinName[$i]);

        // remove last space at the end of the string
        return str($tempName)->squish();
      }
    }

    // If no number is found, return the full string
    return $cabinName;
  }

  /**
   * Get unique cabin names truncated before the first number.
   *
   * @param array $cabinNames
   * @return array
   */
  public static function getUniqueCabinNames(array $cabinNames): array
  {

    $processedNames = array_map(function ($name) {
      return self::getNameBeforeFirstNumber($name);
    }, $cabinNames);

    // Return only unique names
    return array_unique($processedNames);
  }

  /**
   * Return array for table
   * 
   * @return string | null
   */
  public static function getDecks($cat_type_id, $cat_id)
  {
    // based on cat_id return unique decks number coma separated from lowest to highest
    $listOfDecks = Cabin::where('cabin_type_id', $cat_type_id)
      ->where('cabin_category_id', $cat_id)
      ->get();

    $decks = $listOfDecks->map(function ($item) {
      return $item->deck;
    })->filter(function ($deck) {
      return !is_null($deck) && $deck !== '';
    })->unique()->sort()->values();

    return $decks->isEmpty() ? null : $decks->implode(',');
  }

  /**
   * Check the cabins are still available for current category
   * 
   * @return boolean
   */
  public static function checkCabinAvailability($cat_type_id, $cat_id): bool
  {
    // based on cat_id return true if there are still cabins available

    /***
     * TODO: I think we should check strings with enums
     *  
     * 
     */

    $cabins = Cabin::where('cabin_type_id', $cat_type_id)
      ->where('cabin_category_id', $cat_id)
      ->get();

    // if all cabins have status reserved or sold return false
    return $cabins->every(function ($cabin) {
      return $cabin->status === StatusCabin::AVAILABLE->value;
    });
  }
}
