<?php

namespace App\Helpers;

use App\Enums\StatusCabin;
use App\Models\CabinSpec;
use App\Models\Cabin;
use Illuminate\Support\Facades\DB;

// use log
use Illuminate\Support\Facades\Log;

class MatrixHelper
{
  // /**
  //  * Return array for table
  //  *
  //  * @return string | null
  //  */
  public static function getDecks(int $cabinCategoryId, int $cabinTypeId)
  {
    // dump($cabinCategoryId, $ticketType);

    return DB::table('cabins')
      ->join('cabin_specs', 'cabins.cabin_spec_id', '=', 'cabin_specs.id')
      ->where('cabins.cabin_category_id', $cabinCategoryId)
      ->where('cabins.cabin_type_id', $cabinTypeId)
      ->distinct()
      ->pluck('cabin_specs.deck')
      ->implode(',');

    // $cabinSpecIds = Cabin::where('cabin_category_id', $cabinCategoryId)
    //   ->where('cabin_type_id', $ticketType)
    //   ->distinct()
    //   ->pluck('cabin_spec_id') // Only select the cabin_spec_id column
    //   ->toArray();

    // // Get all decks from CabinSpec based on the cabin_spec_ids
    // $decks = CabinSpec::whereIn('id', $cabinSpecIds)
    //   ->distinct()
    //   ->pluck('deck') // Only select the deck column
    //   ->toArray();

    // // Return decks as a comma-separated string
    // return implode(',', $decks);
  }

  public static function getUniqueCategories($categories, $parentCategoryName, $cabinTypeId)
  {
    // Filter categories in memory by parentCategoryName
    $filteredCategories = $categories->filter(fn($item) => $item->category_name === $parentCategoryName);

    // Filter further by ticketType using already loaded cabins
    $filteredCategories = $filteredCategories->filter(
      fn($item) => $item->cabins->contains('cabin_type_id', $cabinTypeId)
    );

    // $testDeck = self::getDecks(14, $cabinTypeId);

    // Log::info('Test deck: ' . $testDeck);

    // Map and transform the filtered categories in one step
    return $filteredCategories
      ->map(function ($item) use ($categories, $cabinTypeId) {
        return [
          'name' => $item->spec->category_name,
          'cabin_category_id' => $item->spec->id,
          'code' => $item->spec->category_code,
          'display_order' => $item->spec->display_order,
          'decks' => self::getDecks($item->spec->id, $cabinTypeId), // Dynamic decks info
          'decks_static' => $item->spec->decks, // Static decks info
          'iframe' => $item->iframe,
          'images' => $item->images,
          'full_title' => $item->getTitleAttribute(),
          'description' => $item->description,
          'price_and_availability' => self::getPriceDetails($categories, $item->category_code),
        ];
      })
      ->unique('code') // Ensure uniqueness by code
      ->values(); // Reset collection keys
  }

  /**
   * Helper function to get price and availability details for a specific capacity
   *
   * @return array
   */
  public static function getPriceDetails($categories, $code)
  {
    $filteredCategories = $categories
      ->where('category_code', $code)
      ->groupBy('capacity')
      ->map(function ($group) {
        return $group->sortBy('display_order')->first();
      })
      ->sortBy('display_order')
      ->values();

    $prices = [];
    for ($capacity = 2; $capacity <= 8; $capacity++) {
      $prices["price_capacity_$capacity"] = self::getSinglePrice($filteredCategories, $capacity);
    }

    return $prices;
  }

  // Single price for a specific capacity
  public static function getSinglePrice($cabins, $capacity)
  {
    // Filter cabins by the specific capacity
    $filteredCabins = $cabins->where('capacity', $capacity);

    // If no cabins match the capacity, return null
    if ($filteredCabins->isEmpty()) {
      return [
        'price' => null,
        'capacity' => $capacity,
        'is_available' => false,
        'cabin_category_id' => null,
      ];
    }

    // Check if any of the cabins are available
    $isAvailable = $filteredCabins->first()->cabins->contains(function ($cabin) {
      return $cabin->status === StatusCabin::AVAILABLE->value;
    });

    // Get first instance just to get category attributes
    // All cabins in the filteredCabins have the same price
    $cabin = $filteredCabins->first();

    return [
      'price' => $cabin->price,
      'capacity' => $capacity,
      'is_available' => $isAvailable,
      'cabin_category_id' => $cabin->id,
    ];
  }
}
