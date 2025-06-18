<?php

namespace App\Repositories;

use App\Enums\StatusCabin;
use App\Interfaces\CabinInterface;
use App\Models\Cabin;
use App\Models\CabinCategory;
use App\Models\CabinSpec;
use App\Models\CabinType;
use App\Traits\ExceptionLogger;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;



class CabinRepository implements CabinInterface
{

  use ExceptionLogger;

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
    DB::beginTransaction();

    try {
      $cabin = new Cabin();
      $cabinSpec = new CabinSpec();

      $cabin->notes = $data['notes'] ?? null;
      $cabin->internal_notes = $data['internal_notes'] ?? null;
      $cabin->tags = !empty($data['tags']) && is_array($data['tags']) ? array_values($data['tags']) : [];

      if (!in_array($cabin->status, [StatusCabin::BOOKED, StatusCabin::PARTIALLY_BOOKED])) {

        if (isset($data['cabin_type']) && $data['cabin_type'] != $cabin->cabin_type_id) {
          if ($cabin->cabin_type_id === 1 && in_array($data['cabin_type'], ["2", "3"])) {
            $cabin->inventory = $cabin->category->capacity;
          } elseif (in_array($cabin->cabin_type_id, [2, 3]) && $data['cabin_type'] === "1") {
            $cabin->inventory = 1;
          }
          $cabin->cabin_type_id = $data['cabin_type'];
        }
        $cabin->cabin_type_id = $data['cabin_type_id']; // Default to type 1 if not provided
        $cabin->status = $data['status'];
        $cabin->cabin_category_id = $data['cabin_category_id'];
        $cabinSpec->cabin_number = $data['cabin_number'];
        $cabinSpec->deck = $data['deck'];
        $cabinSpec->location = $data['location'];
        $cabinSpec->connects_with = $data['connects_with'] ?? null;
        $cabinSpec->total_berths = $data['total_berths'] ?? 0;
        $cabinSpec->lower_bed_type_1 = $data['lower_bed_type_1'] ?? null;
        $cabinSpec->lower_bed_type_2 = $data['lower_bed_type_2'] ?? null;
        $cabinSpec->upper_berths = $data['upper_berths'] ?? null;

        $cabinSpec->accessible = $data['accessible'] ?? false;
        $cabinSpec->balcony = $data['balcony'] ?? false;
        $cabinSpec->obstructed_view = $data['obstructed_view'] ?? false;
      }

      $cabinSpec->save();
      $cabin->cabin_spec_id = $cabinSpec->id;
      $cabin->save();

      DB::commit();
      return $cabin;
    } catch (\Exception $e) {
      DB::rollBack();
      $this->logException($e);
      return null;
    }
  }

  function update(array $data, $id)
  {
    DB::beginTransaction();

    try {
      $cabin = $this->find($id);
      if (!$cabin) {
        throw new \Exception("Cabin not found");
      }

      $cabinSpec = CabinSpec::find($cabin->cabin_spec_id);
      if (!$cabinSpec) {
        throw new \Exception("CabinSpec not found");
      }

      $cabin->notes = $data['notes'] ?? null;
      $cabin->internal_notes = $data['internal_notes'] ?? null;
      $cabin->tags = !empty($data['tags']) && is_array($data['tags']) ? array_values($data['tags']) : [];

      if (!in_array($cabin->status, [StatusCabin::BOOKED, StatusCabin::PARTIALLY_BOOKED])) {

        if (isset($data['cabin_type']) && $data['cabin_type'] != $cabin->cabin_type_id) {
          if ($cabin->cabin_type_id === 1 && in_array($data['cabin_type'], ["2", "3"])) {
            $cabin->inventory = $cabin->category->capacity;
          } elseif (in_array($cabin->cabin_type_id, [2, 3]) && $data['cabin_type'] === "1") {
            $cabin->inventory = 1;
          }
          $cabin->cabin_type_id = $data['cabin_type'];
        }

        $cabin->status = $data['cabin_status'];
        $cabin->cabin_category_id = $data['cabin_category'];
        $cabinSpec->cabin_number = $data['cabin_number'];
        $cabinSpec->deck = $data['deck'];
        $cabinSpec->location = $data['location'];
        $cabinSpec->connects_with = $data['connects_with'] ?? null;
        $cabinSpec->total_berths = $data['total_berths'] ?? 0;
        $cabinSpec->lower_bed_type_1 = $data['lower_bed_type_1'] ?? null;
        $cabinSpec->lower_bed_type_2 = $data['lower_bed_type_2'] ?? null;
        $cabinSpec->upper_berths = $data['upper_berths'] ?? null;

        // Updating features
        $cabinSpec->accessible = $data['features']['accessible'] ?? false;
        $cabinSpec->balcony = $data['features']['balcony'] ?? false;
        $cabinSpec->obstructed_view = $data['features']['obstructed_view'] ?? false;
      }

      $cabin->save();
      $cabinSpec->save();

      DB::commit();
    } catch (\Exception $e) {
      DB::rollBack();
      $this->logException($e);
    }
  }

  function delete($id) {}
  function getCategoriesAndCabins($event_id)
  {
    $categoriesWithCabins = CabinCategory::where('event_id', $event_id)
      ->with([
        'cabins' => function ($query) {
          $query->with([
            'cabinType:id,cabin_type',
            'cabinSpec' => function ($q) {
              $q->select(
                'id',
                'cabin_number',
                'deck',
                'balcony',
                'obstructed_view',
                'location',
                'accessible'
              )->with('cabins:id,cabin_spec_id'); // Avoid N+1 on is_shared_cabin_number
            },
            'temporaryReservations' => function ($q) {
              $q->where('expires_at', '>', now());
            }
          ]);
        }
      ])
      ->withCount([
        'cabins as total_cabins',
        'cabins as available_cabins' => function ($query) {
          $query->where('status', StatusCabin::AVAILABLE->value);
        }
      ])
      ->orderBy('id')
      ->get()
      ->map(function ($category) {
        return [
          'id'             => $category->id,
          'category_type'  => $category->category_type,
          'category_code'  => "{$category->category_code}_{$category->capacity}",
          'category_name'  => $category->category_name,
          'price'          => $category->price,
          'availability'   => "{$category->available_cabins}/{$category->total_cabins}",
          'capacity'       => $category->capacity,
          'title'          => $category->title,
          'display_order'  => $category->display_order,
          'subRows'        => $category->cabins->map(function ($cabin) use ($category) {
            return [
              'id'               => $cabin->id,
              'deck'             => $cabin->cabinSpec?->deck,
              'cabin_number'     => $cabin->cabinSpec?->cabin_number,
              'balcony'          => $cabin->cabinSpec?->balcony ?? false,
              'obstructed_view'  => $cabin->cabinSpec?->obstructed_view ?? false,
              'location'         => $cabin->cabinSpec?->location,
              'accessible'       => $cabin->cabinSpec?->accessible,
              'cabin_status'     => $cabin->status,
              'is_reserved'      => $cabin->temporaryReservations->isNotEmpty(),
              'ticket_inventory' => $cabin->cabinType->id !== 1
                ? "{$cabin->inventory} / {$category->capacity}"
                : $cabin->inventory,
              'cabin_type'       => $cabin->cabinType->cabin_type,
              'is_shared_cabin_number' => $cabin->cabinSpec?->is_shared_cabin_number,
              'cabin_tags'       => $cabin->tags,
            ];
          }),
        ];
      });

    return $categoriesWithCabins;
  }


  function addTags(array $tags, array $cabins) {}

  function getTypes()
  {
    return CabinType::all();
  }

  function getSharedCabins($eventId, $cabinSpecId)
  {
    $cabins = Cabin::with(['category', 'cabinType'])
      ->where('cabin_spec_id', $cabinSpecId)
      ->get();
    if ($cabins->isNotEmpty()) {
      $firstCabin = $cabins->shift();
    }

    $formattedCabins = $cabins->map(function ($cabin) {
      if ($cabin->created_at instanceof Carbon) {
        $cabin->formatted_created_at = $cabin->created_at->format('Y-m-d H:i:s');
      }
      return $cabin;
    });

    //dd($formattedCabins);

    return $formattedCabins;
  }

  function createShared(int $cabinId, int $categoryId)
  {
    DB::beginTransaction();

    try {

      $cabin = Cabin::find($cabinId);
      if (!$cabin) {
        throw new \Exception("Cabin not found");
      }
      $sharedCabin = $cabin->replicate();
      $sharedCabin->cabin_category_id = $categoryId;
      $currentTags = $sharedCabin->tags;
      if (!is_array($currentTags)) {
        $currentTags = [];
      }
      if (!in_array("SHARED", $currentTags)) {
        $currentTags[] = "SHARED";
      }
      $sharedCabin->tags = $currentTags;
      $sharedCabin->save();
      DB::commit();
      //dd($sharedCabin);
      return $sharedCabin;
    } catch (\Exception $e) {
      DB::rollBack();
      $this->logException($e);
      return null;
    }
  }
}
