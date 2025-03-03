<?php

namespace App\Traits;

use App\Models\Cabin;
use Carbon\Carbon;
use App\Enums\StatusCabin;

trait CabinFilter
{
  /**
   * @param int $cabinTypeId
   * @param int|null $cabinCategoryId
   * @param int|null $cabinDeck
   * @param bool $onlyAvailable
   * @param string|null $cabinCategoryCode
   * @param int|null $cabinCapacity
   *
   */

  public function filterCabins(
    $cabinTypeId,
    $cabinCategoryId = null,
    $cabinDeck = null,
    $onlyAvailable = true,
    $cabinCategoryCode = null,
    $cabinCapacity = null
  ) {
    $currentTime = Carbon::now();

    $cabinsQuery = Cabin::with(['category.spec']) // Eager load spec for deck filtering: https://laravel.com/docs/11.x/pennant#eager-loading
      ->where('cabin_type_id', $cabinTypeId)
      ->when($onlyAvailable, function ($query) {
        // Only return cabins that are available
        return $query->where('status', StatusCabin::AVAILABLE->value);
      })
      ->withCount([
        'temporaryReservations as active_reservations_count' => function ($query) use ($currentTime) {
          $query->where('expires_at', '>', $currentTime);
        },
      ]);

    if ($cabinCapacity) {
      $cabinsQuery->whereHas('category.spec', function ($query) use ($cabinCapacity) {
        $query->where('capacity', '=', $cabinCapacity);
      });
    }

    if ($cabinCategoryId) {
      $cabinsQuery->where('cabin_category_id', $cabinCategoryId);
    }

    if ($cabinCategoryCode) {
      $cabinsQuery->whereHas('category.spec', function ($query) use ($cabinCategoryCode) {
        $query->where('category_code', $cabinCategoryCode);
      });
    }

    // Filter by deck using the spec relation
    if ($cabinDeck) {
      $cabinsQuery->whereHas('cabinSpec', function ($query) use ($cabinDeck) {
        $query->where('deck', $cabinDeck);
      });
    }

    $cabins = $cabinsQuery->get();

    if ($cabins->isEmpty()) {
      return [
        'error' => 'No cabins found or already reserved',
        'status' => 404,
      ];
    }

    $formattedCabins = $cabins
      ->filter(function ($cabin) {
        if ($cabin->cabin_type_id == 1) {
          return $cabin->active_reservations_count == 0; // Private cabins must have no active reservations
        } else {
          return $cabin->active_reservations_count < $cabin->inventory; // Single ticket cabins must have available inventory
        }
      })
      ->map(function ($cabin) {
        return [
          'id' => $cabin->id,
          'cabin_number' => (string) $cabin->cabin_number,
          'deck' => $cabin->deck, // Access deck from spec
          'status' => $cabin->status,
          'location' => $cabin->location, // Access location from spec
          'accessible' => $cabin->accessible, // Access accessible from spec
          'balcony' => $cabin->balcony, // Access balcony from spec
          'cabin_type_id' => $cabin->cabin_type_id,
          'cabin_category_id' => $cabin->cabin_category_id,
          'cabin_inventory' => $cabin->inventory,
          'cabin_category_name' => $cabin->category->categoryName,
          'cabin_category_type' => $cabin->category->categoryType,
        ];
      });

    if ($formattedCabins->isEmpty()) {
      return [
        'error' => 'No cabins available after filtering',
        'status' => 404,
      ];
    }

    $formattedCabinsArray = $formattedCabins->values()->toArray();

    return [
      'cabins' => $formattedCabinsArray,
      'status' => 200,
    ];
  }
}
