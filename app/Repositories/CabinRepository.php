<?php

namespace App\Repositories;

use App\Enums\StatusCabin;
use App\Interfaces\CabinInterface;
use App\Models\Cabin;
use App\Models\CabinCategory;
use App\Models\CabinType;
use App\Models\Event;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;

class CabinRepository implements CabinInterface
{
  function getAll()
  {
    // return CabinCategory::all();
  }

  function find($id)
  {
    return Cabin::find($id);
  }

  function save(array $data): ?Cabin
  {
    return new Cabin();
  }
  function update(array $data, $id)
  {
    $cabin = $this->find($id);
    $cabin->notes = $data['notes'] ?? null;
    $cabin->tags = isset($data['tags']) && is_array($data['tags']) && count($data['tags']) > 0
    ? array_values($data['tags'])
    : [];

    if ($cabin->status !== StatusCabin::BOOKED && $cabin->status !== StatusCabin::PARTIALLY_BOOKED) {

      if (isset($data['cabin_type']) && $data['cabin_type'] !== $cabin->cabin_type_id) {
        if ($cabin->cabin_type_id === 1 && ($data['cabin_type'] === "2" || $data['cabin_type'] === "3")) {
          $cabin->inventory = $cabin->category->capacity;
        } elseif (($cabin->cabin_type_id === 3 || $cabin->cabin_type_id === 2) && $data['cabin_type'] === "1") {
          $cabin->inventory = 1;
        }
        $cabin->cabin_type_id = $data['cabin_type'];
      }
      $cabin->status = $data['cabin_status'];
      $cabin->cabin_number = $data['cabin_number'];
      $cabin->cabin_category_id = $data['cabin_category'];
      $cabin->cabin_type_id = $data['cabin_type'];
      $cabin->deck = $data['deck'];
      $cabin->location = $data['location'];
      $cabin->connects_with = $data['connects_with'] ?? null;
      $cabin->total_berths = $data['total_berths'] ?? null;
      $cabin->lower_bed_type_1 = $data['lower_bed_type_1'] ?? null;
      $cabin->lower_bed_type_2 = $data['lower_bed_type_2'] ?? null;
      $cabin->upper_berths = $data['upper_berths'] ?? null;

      // updating features
      $cabin->accessible = $data['features']['accessible'];
      $cabin->balcony = $data['features']['balcony'];
      $cabin->obstructed_view = $data['features']['obstructed_view'];
    }
    $cabin->save();
  }
  function delete($id) {}

  function getCategoriesAndCabins($event_id)
  {
    // Eager load only for cabins and cabinType
    $categoriesWithCabins = CabinCategory::where('event_id', '=', $event_id)->with(['cabins.cabinType'])
      ->get()
      ->map(function ($category) {
        // Count total and availables
        $totalCabins = $category->cabins->count();
        $availableCabins = $category->cabins->where('status', StatusCabin::AVAILABLE->value)->count();

        return [
          'id' => $category->id,
          'category_type' => $category->category_type,
          'category_code' => "{$category->category_code}_{$category->capacity}",
          'category_name' => $category->category_name,
          'price'         => $category->price,
          'availability'  => "{$availableCabins}/{$totalCabins}",
          'capacity'      => $category->capacity,
          'title'         => $category->title,
          'display_order' => $category->display_order,
          'subRows'       => $category->cabins->map(function ($cabin) use ($category) {
            $ticketInventory = $cabin->inventory;
            // If cabin type is not PRIVATE CABIN, then show the split tickets
            // e.g. 2/4, 3/6, 4/8
            if ($cabin->cabinType->id !== 1) {
              $ticketInventory = "{$ticketInventory} / {$category->capacity}";
            }

            return [
              'id' => $cabin->id,
              'deck' => $cabin->deck,
              'total_berths'    => $cabin->total_berths,
              'cabin_number'    => $cabin->cabin_number,
              'cabin_status'    => $cabin->status,
              'ticket_inventory' => $ticketInventory,
              'cabin_type'      => $cabin->cabinType->cabin_type,
              'cabin_tags'      => $cabin->tags
            ];
          })
        ];
      })
      ->sortBy('display_order');

    return $categoriesWithCabins;
  }

  function addTags(array $tags, array $cabins) {}

  function getTypes(){
    return CabinType::all();
  }
}
