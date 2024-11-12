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
    $currentTime = Carbon::now();

    $cabinsQuery = Cabin::with("category")
      ->where("cabin_type_id", $cabinTypeId)
      ->where("cabin_category_id", $cabinCategoryId)
      ->where("status", StatusCabin::AVAILABLE->value)
      ->withCount([
        "temporaryReservations as active_reservations_count" => function (
          $query
        ) use ($currentTime) {
          $query->where("expires_at", ">", $currentTime);
        },
      ]);

    if ($cabinDeck) {
      $cabinsQuery->where("deck", $cabinDeck);
    }

    $cabins = $cabinsQuery->get();

    if ($cabins->isEmpty()) {
      return [
        "error" => "No cabins found or already reserved",
        "status" => 404,
      ];
    }

    $formattedCabins = $cabins
      ->filter(function ($cabin) {
        if ($cabin->cabin_type_id == 1) {
          return $cabin->active_reservations_count == 0;
        } else {
          return $cabin->active_reservations_count < $cabin->inventory;
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

    if ($formattedCabins->isEmpty()) {
      return [
        "error" => "No cabins available after filtering",
        "status" => 404,
      ];
    }

    return ["cabins" => $formattedCabins, "status" => 200];
  }
}
