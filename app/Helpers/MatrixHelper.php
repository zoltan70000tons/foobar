<?php

namespace App\Helpers;

use App\Enums\StatusCabin;

class MatrixHelper
{
  public static function getUniqueDecks($cabins)
  {
    $decks = $cabins
      ->map(function ($cabin) {
        return $cabin->cabinSpec->deck;
      })
      ->unique()
      ->sort();

    return $decks->implode(',');
  }

  public static function getUniqueCategories($categories, $parentCategoryName, $ticketType)
  {
    // Filter categories by parent category name and ticket type
    $filteredCategories = $categories
      ->where('category_name', $parentCategoryName)
      ->filter(fn($category) => $category->cabins->contains('cabin_type_id', $ticketType));

    // Group the filtered categories by their spec code to ensure uniqueness by code
    $groupedByCode = $filteredCategories->groupBy(fn($category) => $category->spec->category_code);

    // Map each group to a single entry, merging decks across all categories in the group
    $result = $groupedByCode->map(function ($group) use ($categories) {
      $firstCategory = $group->first();

      // $allCabins = $group->flatMap(fn($category) => $category->cabins);

      //$decks = self::getUniqueDecks($allCabins);

      return [
        'name' => $firstCategory->spec->category_name,
        'cabin_category_id' => $firstCategory->spec->id,
        'code' => $firstCategory->spec->category_code,
        'display_order' => $firstCategory->spec->display_order,
        //'decks' => $decks,
        'decks_static' => $firstCategory->spec->decks, // your static label if needed
        'iframe' => $firstCategory->iframe,
        'images' => $firstCategory->images,
        'full_title' => $firstCategory->getTitleAttribute(),
        'description' => $firstCategory->description,
        'price_and_availability' => self::getPriceDetails($categories, $firstCategory->category_code),
      ];
    });

    // Sort the result if needed and reset collection keys
    return $result->sortBy('display_order')->values();
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

    return collect(range(2, 8))
      ->mapWithKeys(
        fn($capacity) => [
          "price_capacity_$capacity" => self::getSinglePrice($filteredCategories, $capacity),
        ]
      )
      ->toArray();
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
        'decks' => null,
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
      'decks' => self::getUniqueDecks($cabin->cabins),
      'is_available' => $isAvailable,
      'cabin_category_id' => $cabin->id,
    ];
  }
}
