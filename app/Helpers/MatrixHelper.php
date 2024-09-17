<?php

namespace App\Helpers;

use Illuminate\Support\Str;
use App\Models\Cabin;
use App\Enums\StatusCabin;

// use log
use Illuminate\Support\Facades\Log;

class MatrixHelper
{
  /**
   * Return array for table
   * 
   * @return string | null
   */
  public static function getDecks($cat_type_id, $cabinTypeId)
  {
      $decks = Cabin::where('cabin_type_id', $cabinTypeId)
          ->where('cabin_category_id', $cat_type_id)
          ->whereNotNull('deck')
          //->where('deck', '!=', '')
          ->distinct()
          ->orderBy('deck')
          ->pluck('deck')
          ->map(function ($deck) {
              return strtolower(trim($deck));
          })
          ->unique()
          ->values();
  
      return $decks->isEmpty() ? null : $decks->implode(',');
  }

  /**
   * Check the cabins are still available for current category
   * 
   * @return boolean
   */
  public static function checkCabinAvailability($cat_type_id, $ticketType): bool
  {
      return Cabin::where('cabin_type_id', $ticketType)
          ->where('cabin_category_id', $cat_type_id)
          ->where('status', StatusCabin::AVAILABLE->value)
          ->exists();
  }


  /**
   * Return unique cabins type where cabin_code and decks are the same
   * 
   * @return array
   */
  public static function getUniqueCabinCodes($cabins, $ticketType)
  {
      return $cabins->map(function ($item) use ($ticketType) {
          $decks = self::getDecks($item->id, $ticketType);
  
          if ($decks === null) {
              return null;
          }
  
          return [
              'cabin_category_id' => $item->id,
              'code' => $item->category_code,
              'display_order' => $item->display_order,
              'decks' => $decks,
          ];
      })->filter()->unique('code')->values();
  }

  /**
   * Helper function to get price and availability details for a specific capacity
   * 
   * @return array
   */
  public static function getPriceDetails($cabins, $capacity, $availabilityStatus)
  {
    $cabin = $cabins->where('capacity', $capacity)->first();
    return [
      "full_category_name" => $cabin ? $cabin->category_name : null,
      "capacity" => $capacity,
      "price" => $cabin ? $cabin->price : null,
      "is_available" => $cabin ? $availabilityStatus : null,
    ];
  }

  /**
   * Helper function to get max capcity for a specific category
   * 
   * @return array
   */
  public static function getMaxCapacity($cabinType, $ticketType)
  {

    $cabins = Cabin::where('cabin_type_id', $ticketType)
    ->where('cabin_category_id', $cabinType)
    ->get();

    $maxCapacity = $cabins->max('capacity');

    return $maxCapacity;
  }
}