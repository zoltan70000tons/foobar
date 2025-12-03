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

    public static function getUniqueCategoriesUnified(
        $categories,
        $parentCategoryName,
        $cabinTypeId,
        $reservationsCabinNumbers
    ) {
        // If cabinTypeId is provided → filter
        $filteredCategories = $categories
            ->where('category_name', $parentCategoryName)
            ->when($cabinTypeId, fn($q) =>
            $q->filter(fn($c) => $c->cabins->contains('cabin_type_id', $cabinTypeId))
            );

        $groupedByCode = $filteredCategories->groupBy(fn($c) => $c->spec->category_code);

        return $groupedByCode->map(function ($group) use ($categories, $cabinTypeId, $reservationsCabinNumbers) {
            $first = $group->first();

            return [
                'name'                => $first->spec->category_name,
                'cabin_category_id'   => $first->spec->id,
                'code'                => $first->spec->category_code,
                'display_order'       => $first->spec->display_order,
                'decks_static'        => $first->spec->decks,
                'full_title'          => $first->getTitleAttribute(),
                'description'         => $first->description,
                'price_and_availability' =>
                    MatrixHelper::getPriceDetailsUnified(
                        $categories,
                        $first->category_code,
                        $cabinTypeId,
                        $reservationsCabinNumbers
                    ),
            ];
        })
            ->sortBy('display_order')
            ->values();
    }

    public static function getPriceDetailsUnified(
        $categories,
        $code,
        $cabinTypeId,
        $reservationsCabinNumbers
    ) {
        $filteredCategories = $categories
            ->where('category_code', $code)
            ->groupBy('capacity')
            ->map(fn($group) => $group->sortBy('display_order')->first())
            ->values();

        return collect(range(2, 8))
            ->mapWithKeys(fn($capacity) => [
                "price_capacity_$capacity" =>
                    self::getSinglePriceUnified(
                        $filteredCategories,
                        $capacity,
                        $cabinTypeId,
                        $reservationsCabinNumbers
                    ),
            ])
            ->toArray();
    }

    public static function getSinglePriceUnified(
        $cabins,
        $capacity,
        $cabinTypeId,
        $reservationsCabinNumbers
    ) {
        $filteredCabins = $cabins->where('capacity', $capacity);

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

        $isAvailable = $filteredCabins->first()->cabins->contains(function ($cabin) use ($reservationsCabinNumbers, $cabinTypeId) {
            $byStatus =
                $cabin->status === StatusCabin::AVAILABLE ||
                $cabin->status === StatusCabin::PARTIALLY_BOOKED;

            $byReservation = MatrixHelper::isAvailableByReservationUnified(
                $cabin,
                $reservationsCabinNumbers,
                $cabinTypeId
            );

            return $byStatus && $byReservation;
        });

        $cabin = $filteredCabins->first();
        $title = $cabin->getTitleAttribute() . ' ' . $cabin->capacityDescription;

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
            'full_title' => $title,
            'images' => $cabin->images,
            'iframe' => $cabin->iframe,
            'description' => $cabin->description,
            'inventory' => $inv,
        ];
    }

    public static function isAvailableByReservationUnified(
        $cabin,
        $reservationsCabinNumbers,
        $cabinTypeId
    ) {
        // if cabinTypeId not given → derive from cabin
        $ticketType = $cabinTypeId ?? $cabin->cabin_type_id;

        if ($ticketType == 1) {
            return !in_array($cabin->cabin_number, $reservationsCabinNumbers);
        }

        $same = collect($reservationsCabinNumbers)
            ->filter(fn($n) => $n === $cabin->cabin_number)
            ->count();

        return $same < $cabin->inventory;
    }
}

