<?php

namespace App\Repositories;

use App\Enums\StatusCabin;
use App\Interfaces\CabinInterface;
use App\Models\Cabin;
use App\Models\CabinCategory;
use App\Models\CabinSpec;
use App\Models\CabinType;
use App\Traits\ExceptionLogger;
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
    return new Cabin();
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
    // Fetch all categories and cabins with necessary relationships
    $categoriesWithCabins = CabinCategory::where('event_id', $event_id)
      ->with([
        'cabins' => function ($query) {
          $query->with([
            'cabinType:id,cabin_type',
            'cabinSpec:id,cabin_number,deck,balcony,obstructed_view,location,accessible'
          ])
            ->leftJoin('temporary_reservations as tr', 'tr.cabin_id', '=', 'cabins.id')
            ->select([
              'cabins.id',
              'cabins.cabin_category_id',
              'cabins.cabin_spec_id',
              'cabins.inventory',
              'cabins.status',
              'cabins.cabin_type_id',
              DB::raw('CASE WHEN tr.id IS NOT NULL THEN true ELSE false END as is_reserved')
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
              'is_reserved'      => (bool) $cabin->is_reserved,
              'ticket_inventory' => $cabin->cabinType->id !== 1
                ? "{$cabin->inventory} / {$category->capacity}"
                : $cabin->inventory,
              'cabin_type'       => $cabin->cabinType->cabin_type,
              'cabin_tags'       => $cabin->tags,
            ];
          })
        ];
      });

    return $categoriesWithCabins;
  }


  function addTags(array $tags, array $cabins) {}

  function getTypes(){
    return CabinType::all();
  }
}
