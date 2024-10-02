<?php

namespace App\Helpers;

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
  public static function getDecks($cabins)
  {
        $decks = $cabins->pluck('deck')->unique()->values()->all();
        
        // to string with coma
        return implode(',', $decks);
  }

    /**
     * Return unique cabins type where cabin_number and decks are the same
     * 
     * @return array
     */
    public static function getUniqueCategories($categories, $parentCategoryName, $ticketType)
    {
        $filteredCategories = $categories->filter(function ($item) use ($parentCategoryName) {
            return $item->category_name === $parentCategoryName;
        });

        // from filtered categories return only these which cabins have cabin_type_id equal to ticketType
        $filteredCategoriesWithCabins = $filteredCategories->filter(function ($item) use ($ticketType) {
            return $item->cabins->where('cabin_type_id', $ticketType)->isNotEmpty();
        });

        // dd($filteredCategories->toArray());

        return $filteredCategoriesWithCabins->map(function ($item) use ($categories) {
            return [
                'name' => $item->category_name,
                'cabin_category_id' => $item->id,
                'code' => $item->category_code,
                'display_order' => $item->display_order,
                'decks' => self::getDecks($item->cabins),
                'price_and_availability' => self::getPriceDetails($categories, $item->category_code),
            ];
        })->unique('code')->values();
    }


  /**
   * Helper function to get price and availability details for a specific capacity
   * 
   * @return array
   */
    public static function getPriceDetails($categories, $code)
    {

        $filteredCategories = $categories->where('category_code', $code)
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
                "price" => null,
                "capacity" => $capacity,
                "is_available" => false,
            ];
        }

        // Check if all cabins for this capacity are available
        $isAvailable = $filteredCabins->every(function ($cabin) {
            return $cabin->status !== StatusCabin::AVAILABLE->value;
        });

        // Retrieve price details for the first available cabin with this capacity
        $cabin = $filteredCabins->first();

        return [
            "price" => $cabin->price,
            "capacity" => $capacity,
            "is_available" => $isAvailable,
        ];
    }
}

