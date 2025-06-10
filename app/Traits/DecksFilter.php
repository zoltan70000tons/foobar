<?php

namespace App\Traits;

use App\Models\Cabin;
use App\Enums\StatusCabin;
use App\Models\TemporaryReservation;

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

    $decksQuery = Cabin::with(['category.spec'])
      ->where('cabin_type_id', $cabinTypeId)
      ->whereNotIn('cabin_number', $tempReservedCabinNumbers)
      ->when($onlyAvailable, function ($query) use ($cabinTypeId) {
        if ($cabinTypeId == 1) {
          $query->where('status', StatusCabin::AVAILABLE->value);
        } else {
          $query->whereIn('status', [StatusCabin::AVAILABLE->value, StatusCabin::PARTIALLY_BOOKED->value]);
        }
      });

    if ($cabinCategoryCode) {
      $decksQuery->whereHas('category.spec', function ($query) use ($cabinCategoryCode) {
        $query->where('category_code', $cabinCategoryCode);
      });
    }

    if ($cabinCapacity) {
      $decksQuery->whereHas('category.spec', function ($query) use ($cabinCapacity) {
        $query->where('capacity', '=', $cabinCapacity);
      });
    }

    // prepare only by unqiue decks
    $decks = $decksQuery->get()->unique(function ($item) {
      return $item->cabin->spec->deck;
    })->sortBy('cabin.spec.deck');

    // Map to get only the deck numbers
    $deckNumbers = $decks->pluck('cabin.spec.deck')->unique()->values();
    return $deckNumbers->toArray();

  }
}