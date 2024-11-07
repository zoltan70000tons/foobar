<?php

namespace App\Traits;

use App\Models\Cabin;
use Carbon\Carbon;
use App\Enums\StatusCabin;

trait CabinFilter
{
  public function filterCabins(
    $cabinTypeId,
    $cabinCategoryId,
    $cabinDeck = null
  ) {
    // Fetch cabins with the given filters and exclude those with active temporary reservations
    $cabinsQuery = Cabin::with("category")
      ->where("cabin_type_id", $cabinTypeId)
      ->where("cabin_category_id", $cabinCategoryId);

    // If cabinDeck is provided, apply the deck filter
    if ($cabinDeck) {
      $cabinsQuery->where("deck", $cabinDeck);
    }

    // Exclude cabins with active temporary reservations
    $cabins = $cabinsQuery
      ->whereDoesntHave("temporaryReservations", function ($query) {
        $query->where("expires_at", ">", Carbon::now());
      })
      ->get();

    if ($cabins->isEmpty()) {
      return [
        "error" => "No cabins found or already reserved",
        "status" => 404,
      ];
    }

    // Filter based on cabin type, status, and active reservations vs. inventory
    $formattedCabins = $cabins
      ->filter(function ($cabin) {
        if ($cabin->cabin_type_id == 1) {
          return $cabin->status === StatusCabin::AVAILABLE->value;
        } else {
          $activeReservations = $cabin
            ->temporaryReservations()
            ->where("expires_at", ">", Carbon::now())
            ->count();
          return $cabin->status === StatusCabin::AVAILABLE->value &&
            $activeReservations < $cabin->inventory;
        }
      })
      ->map(function ($cabin) {
        return [
          "id" => $cabin->id,
          "cabin_number" => (string) $cabin->cabin_number,
          "deck" => $cabin->deck,
          "status" => $cabin->status,
          "location" => $cabin->location,
          "accessible" => $cabin->accessible,
          "balcony" => $cabin->balcony,
          "cabin_type_id" => $cabin->cabin_type_id,
          "cabin_category_id" => $cabin->cabin_category_id,
          "cabin_inventory" => $cabin->inventory,
          "cabin_category_name" => $cabin->category->category_name,
        ];
      });

    return ["cabins" => $formattedCabins, "status" => 200];
  }
}
