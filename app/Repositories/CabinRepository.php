<?php

namespace App\Repositories;

use App\Enums\StatusCabin;
use App\Interfaces\CabinInterface;
use App\Models\Cabin;
use App\Models\CabinCategory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CabinRepository implements CabinInterface
{
    function getAll()
    {
       // return CabinCategory::all();
    }

    function find($id)
    {
       // return CabinCategory::find($id);
    }

    function save(array $data): ?Cabin
    {
      return new Cabin();

    }
    function update(array $data, $id)
    {
       
    }
    function delete($id) {
        
    }

    function getCategoriesAndCabins() {
        $categoriesWithCabins = CabinCategory::with('cabins')->get()->map(function($category) {
            $totalCabins = $category->cabins->count();
            $availableCabins = $category->cabins->filter(function($cabin) {
                return $cabin->status === StatusCabin::AVAILABLE->value;
            })->count();
            return [
                'id' => $category->id,
                'category_type' => $category->category_type,
                'category_code' => $category->category_code,
                'category_name' => $category->category_name,
                'price' => $category->price,
                'status' => "{$availableCabins}/{$totalCabins}",
                'capacity' => $category->capacity,
                'subRows' => $category->cabins->map(function($cabin) {
                    return [
                        'cabin_code' => $cabin->cabin_code,
                        'deck' => $cabin->deck,
                        'total_berths' => $cabin->total_berths,
                        'cabin_number' => $cabin->cabin_number,
                        'cabin_deck' => $cabin->deck,
                        'cabin_status' => $cabin->status,
                        'cabin_type' => $cabin->cabinType->cabin_type,
                    ];
                })
            ];
        });
        return $categoriesWithCabins;
    }
    

}
