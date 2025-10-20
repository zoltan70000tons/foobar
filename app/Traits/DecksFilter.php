<?php

namespace App\Traits;

use App\Models\Cabin;
use App\Enums\StatusCabin;
use App\Models\TemporaryReservation;
use Log;

trait DecksFilter
{
  /**
   * @param int $cabinTypeId
   * @param int|null $cabinCategoryCode
   * @param bool $onlyAvailable
   * @param int|null $cabinCapacity
   *
   */
  public function filterDecks(
    $cabinTypeId,
    $cabinCategoryCode = null,
    $onlyAvailable = true,
    $cabinCapacity = null
  ) {

    $tempReservedCabinNumbers = TemporaryReservation::pluck('cabin_number')->toArray();

    $decksQuery = Cabin::with(['cabinType', 'cabinSpec', 'category.spec'])
      ->whereDoesntHave('cabinSpec', function ($query) use ($tempReservedCabinNumbers) {
        $query->whereIn('cabin_number', $tempReservedCabinNumbers);
      })
      ->where('cabin_type_id', $cabinTypeId)
      ->when($onlyAvailable, function ($query) use ($cabinTypeId) {
        if ($cabinTypeId == 1) {
          $query->where('status', StatusCabin::AVAILABLE);
        } else {
          $query->whereIn('status', [StatusCabin::AVAILABLE, StatusCabin::PARTIALLY_BOOKED]);
        }
      });

    if ($cabinCategoryCode) {
      $decksQuery->whereHas('category.spec', function ($query) use ($cabinCategoryCode) {
        $query->where('category_code', '=', $cabinCategoryCode);
      });
    }

    if ($cabinCapacity) {
      $decksQuery->whereHas('category.spec', function ($query) use ($cabinCapacity) {
        $query->where('capacity', '=', $cabinCapacity);
      });
    }

    $decks = $decksQuery->get();

    // return only decks numbers 
    $decksNumbers = $decks->pluck('deck')->unique()->sort()->values();

    return $decksNumbers->toArray();


  }
}