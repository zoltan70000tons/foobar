<?php

namespace App\Traits;

use App\Models\Cabin;
use Carbon\Carbon;
use App\Enums\StatusCabin;
use App\Models\TemporaryReservation;

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
    $cabinCapacity = null,
    $returnInProgress = false // Used to show in progress cabins as not available
  ) {
    $currentTime = Carbon::now();

    $cabinsQuery = Cabin::with(['category.spec']) // Eager load spec for deck filtering: https://laravel.com/docs/11.x/pennant#eager-loading
      ->where('cabin_type_id', $cabinTypeId)
      ->when($onlyAvailable, function ($query) use ($cabinTypeId) {
        // Only return cabins that are available or PARTIALLY_BOOKED
        if ($cabinTypeId == 1) {
          $query->where('status', StatusCabin::AVAILABLE->value);
        } else {
          $query->whereIn('status', [StatusCabin::AVAILABLE->value, StatusCabin::PARTIALLY_BOOKED->value]);
        }
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
      if (!$cabinTypeId) {
        return [
          'cabins' => [],
          'status' => 200,
        ];
      }
      return [
        'error' => 'No cabins found or already reserved',
        'status' => 404,
      ];
    }


    $temporarilyReservedCabinNumbers = [];

    if ($cabinTypeId == 1) {
      $temporarilyReservedCabinNumbers = TemporaryReservation::where('expires_at', '>', $currentTime)
        ->pluck('cabin_number')
        ->unique()
        ->toArray();
    }

    $formattedCabins = $cabins
      ->filter(function ($cabin) use ($temporarilyReservedCabinNumbers) {
        if ($cabin->cabin_type_id == 1) {
          // For private cabins, exclude if the itself or cabin_number is reserved
          return !in_array($cabin->cabin_number, $temporarilyReservedCabinNumbers) && $cabin->active_reservations_count == 0;
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
          'lower_bed_type_2' => $cabin->lowerBedType2, // Access lower bed type 2 from spec
          'cabin_type_id' => $cabin->cabin_type_id,
          'cabin_category_id' => $cabin->cabin_category_id,
          'cabin_inventory' => $cabin->inventory,
          'cabin_category_name' => $cabin->category->categoryName,
          'cabin_category_type' => $cabin->category->categoryType,
        ];
      });

    // Prioritize PARTIALLY_BOOKED cabins first for non-private types
    if ($cabinTypeId !== 1) {
      $formattedCabins = $formattedCabins->sortByDesc(function ($cabin) {
        return $cabin['status'] === StatusCabin::PARTIALLY_BOOKED->value ? 1 : 0;
      })->values();
    }

    if ($returnInProgress && $cabinTypeId == 1) {
      $inProgressCabins = $cabins
        ->filter(function ($cabin) use ($temporarilyReservedCabinNumbers) {
          return in_array($cabin->cabin_number, $temporarilyReservedCabinNumbers);
        })
        ->reject(function ($cabin) use ($formattedCabins) {
          return $formattedCabins->contains('id', $cabin->id);
        })
        ->map(function ($cabin) {
          return [
            'id' => $cabin->id,
            'cabin_number' => (string) $cabin->cabin_number,
            'deck' => $cabin->deck,
            'status' => 'IN PROGRESS', // pseudo status for in-progress cabins
            'location' => $cabin->location,
            'accessible' => $cabin->accessible,
            'balcony' => $cabin->balcony,
            'lower_bed_type_2' => $cabin->lowerBedType2,
            'cabin_type_id' => $cabin->cabin_type_id,
            'cabin_category_id' => $cabin->cabin_category_id,
            'cabin_inventory' => $cabin->inventory,
            'cabin_category_name' => $cabin->category->categoryName,
            'cabin_category_type' => $cabin->category->categoryType,
          ];
        });

      // Append to the available cabins list
      $formattedCabins = $formattedCabins->merge($inProgressCabins);
    }
    if ($formattedCabins->isEmpty()) {
      return [
        'error' => __('feedback.cabin_not_available'),
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
