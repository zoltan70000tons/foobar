<?php

namespace App\Helpers;

use App\Enums\StatusCabin;
use App\Models\CabinCategory;
use App\Models\TemporaryReservation;

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

  public static function getUniqueCategories($categories, $parentCategoryName, $ticketType, $reservationsCabinNumbers)
  {
    // Filter categories by parent category name and ticket type
    $filteredCategories = $categories
      ->where('category_name', $parentCategoryName)
      ->filter(fn($category) => $category->cabins->contains('cabin_type_id', $ticketType));

    // Group the filtered categories by their spec code to ensure uniqueness by code
    $groupedByCode = $filteredCategories->groupBy(fn($category) => $category->spec->category_code);

    // Map each group to a single entry, merging decks across all categories in the group
    $result = $groupedByCode->map(function ($group) use ($categories, $ticketType, $reservationsCabinNumbers) {
      $firstCategory = $group->first();

      // $allCabins = $group->flatMap(fn($category) => $category->cabins);

      //$decks = self::getUniqueDecks($allCabins);

      return [
        'name' => $firstCategory->spec->category_name,
        'cabin_category_id' => $firstCategory->spec->id,
        'code' => $firstCategory->spec->category_code,
        'display_order' => $firstCategory->spec->display_order,
        'decks_static' => $firstCategory->spec->decks, // your static label if needed
        'full_title' => $firstCategory->getTitleAttribute(),
        'description' => $firstCategory->description,
        'price_and_availability' => self::getPriceDetails($categories, $firstCategory->category_code, $ticketType, $reservationsCabinNumbers),
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
  public static function getPriceDetails($categories, $code, $ticketType, $reservationsCabinNumbers)
  {
    $filteredCategories = $categories
      ->where('category_code', $code)
      ->groupBy('capacity')
      ->map(fn($group) => $group->sortBy('display_order')->first())
      ->values();

    return collect(range(2, 8))
      ->mapWithKeys(
        fn($capacity) => [
          "price_capacity_$capacity" => self::getSinglePrice($filteredCategories, $capacity, $ticketType, $reservationsCabinNumbers),
        ]
      )
      ->toArray();
  }

  // Single price for a specific capacity
  public static function getSinglePrice($cabins, $capacity, $ticketType, $reservationsCabinNumbers)
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
        'full_title' => null,
        'images' => null,
        'iframe' => null,
      ];
    }

    // Check if any of the cabins are available
    $isAvailable = $filteredCabins->first()->cabins->contains(function ($cabin) use ($reservationsCabinNumbers, $ticketType) {
      $byStatus = $cabin->status === StatusCabin::AVAILABLE ||
        $cabin->status === StatusCabin::PARTIALLY_BOOKED;

      // if single cabin number is in array of reservations, then it is not available
      $byReservation = self::isAvailableByReservation($cabin, $reservationsCabinNumbers, $ticketType);

      $available = $byStatus && $byReservation;

      return $available;

    });

    // Get first instance just to get category attributes
    // All cabins in the filteredCabins have the same price
    $cabin = $filteredCabins->first();
    $category_full_title = $cabin->getTitleAttribute() . ' ' . $cabin->capacityDescription;

      $inv = [
          StatusCabin::AVAILABLE->value => 0,
          StatusCabin::RESERVED->value => 0,
          StatusCabin::BOOKED->value => 0,
          StatusCabin::CLOSED->value => 0,
          StatusCabin::PARTIALLY_BOOKED->value => 0,
          "IP" => 0,
      ];

      foreach ($cabin->cabins as $singleCabin) {
          $inv[$singleCabin->status->value] += 1;

          $foo = TemporaryReservation::query()->where("cabin_id", "=", $singleCabin->id)->count();
          $inv["IP"] = $foo;
      }

    return [
        'price' => $cabin->price,
        'capacity' => $capacity,
        'decks' => self::getUniqueDecks($cabin->cabins),
        'is_available' => $isAvailable,
        'cabin_category_id' => $cabin->id,
        'full_title' => $category_full_title,
        'images' => $cabin->images,
        'iframe' => $cabin->iframe,
        'description' => $cabin->description,
        'inventory' => $inv,
    ];
  }


  /**
  * Available by reservation check
  * If the ticket type is 1 (Single Ticket), then the cabin must not be in the reservations list.
  * If the ticket type is not 1 (Private Cabin), then we collect same reservation numbers and compare it with the cabin inventory.
  * If the count of same reservation numbers is less than the cabin inventory, then it is available.
  */
  public static function isAvailableByReservation($cabin, $reservationsCabinNumbers, $ticketType)
  {
    // Check if the cabin is available by checking its status and reservation numbers
    if ($ticketType == 1) {
      return !in_array($cabin->cabin_number, $reservationsCabinNumbers);
    } else {
      $sameReservationCount = collect($reservationsCabinNumbers)->filter(function ($number) use ($cabin) {
        return $number === $cabin->cabin_number;
      })->count();

      return $sameReservationCount < $cabin->inventory;
    }
  }
}

