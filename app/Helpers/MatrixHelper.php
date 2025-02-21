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
  public static function getDecks(array $cabinCategoryIds, int $ticketType)
  {
    return DB::table('cabins')
      ->join('cabin_specs', 'cabins.cabin_spec_id', '=', 'cabin_specs.id')
      ->whereIn('cabins.cabin_category_id', $cabinCategoryIds) // Batch fetch for all categories
      ->where('cabins.cabin_type_id', $ticketType)
      ->distinct()
      ->pluck('cabin_specs.deck')
      ->implode(',');
  }


  public static function getUniqueCategories($categories, $parentCategoryName, $ticketType)
  {
    // Filter categories once and extract IDs upfront
    $filteredCategories = $categories->filter(fn($item) => $item->category_name === $parentCategoryName);
    $cabinCategoryIds = $filteredCategories->pluck('spec.id')->toArray();

    // Filter further by ticketType using already loaded cabins
    $filteredCategories = $filteredCategories->filter(
      fn($item) => $item->cabins->contains('cabin_type_id', $ticketType)
    );

    // Fetch decks in one batch query
    $decks = self::getDecks($cabinCategoryIds, $ticketType);

    return $filteredCategories
      ->map(fn($item) => [
        'name' => $item->spec->category_name,
        'cabin_category_id' => $item->spec->id,
        'code' => $item->spec->category_code,
        'display_order' => $item->spec->display_order,
        'decks' => $decks, // Use Dynamic pre-fetched decks
        'decks_static' => $item->spec->decks, // Static decks label
        'iframe' => $item->iframe,
        'images' => $item->images,
        'full_title' => $item->getTitleAttribute(),
        'description' => $item->description,
        'price_and_availability' => self::getPriceDetails($categories, $item->category_code),
      ])
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
      ->map(fn($group) => $group->sortBy('display_order')->first())
      ->values();

    return collect(range(2, 8))->mapWithKeys(fn($capacity) => [
      "price_capacity_$capacity" => self::getSinglePrice($filteredCategories, $capacity),
    ])->toArray();
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
