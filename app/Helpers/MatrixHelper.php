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
  public static function getDecks(string $categoryCode, int $ticketType)
  {
    static $cache = [];

    $key = "$categoryCode-$ticketType";
    if (!isset($cache[$key])) {
      $cache[$key] = DB::table('cabins')
        ->join('cabin_categories', 'cabins.cabin_category_id', '=', 'cabin_categories.id')
        ->join('cabin_category_specs', 'cabin_categories.cabin_category_spec_id', '=', 'cabin_category_specs.id')
        ->join('cabin_specs', 'cabins.cabin_spec_id', '=', 'cabin_specs.id')
        ->where('cabins.cabin_type_id', $ticketType)
        ->where('cabin_category_specs.category_code', $categoryCode)
        ->distinct()
        ->pluck('cabin_specs.deck')
        ->implode(',');
    }

    return $cache[$key];
  }


  public static function getUniqueCategories($categories, $parentCategoryName, $ticketType)
  {
    $filteredCategories = $categories
      ->where('category_name', $parentCategoryName)
      ->filter(fn($category) => $category->cabins->contains('cabin_type_id', $ticketType));

    return $filteredCategories->map(fn($category) => [
      'name' => $category->spec->category_name,
      'cabin_category_id' => $category->spec->id,
      'code' => $category->spec->category_code,
      'display_order' => $category->spec->display_order,
      'decks' => self::getDecks($category->spec->category_code, $ticketType), // Use Dynamic pre-fetched decks
      'decks_static' => $category->spec->decks, // Static decks label
      'iframe' => $category->iframe,
      'images' => $category->images,
      'full_title' => $category->getTitleAttribute(),
      'description' => $category->description,
      'price_and_availability' => self::getPriceDetails($categories, $category->category_code),
    ])
      ->unique('code') // Ensure uniqueness by code
      ->values();
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
