<?php

namespace App\Http\Controllers\Api\Customer;

use App\Helpers\MatrixHelper;
use App\Http\Controllers\Controller;

use App\Models\CabinType;
use App\Models\CabinCategory;

use Illuminate\Support\Facades\Log;

class PricingMatrixController extends Controller
{

  protected $cabinType;
  protected $cabinCategory;

  public function __construct(CabinType $cabinType, CabinCategory $cabinCategory)
  {
    $this->cabinType = $cabinType;
    $this->cabinCategory = $cabinCategory;
  }

  /**
   * Index return only name and id of cabin types
   * 
   */
  public function index()
  {
    $cabinTypes = $this->cabinType->select('id', 'cabin_type')->get();
    return response()->json($cabinTypes);
  }

  /**
   * Show cabins by id
   * 
   * default cabin: 1 
   * - 1 is private cabin, 
   * - 2 is male, 
   * - 3 is female
   * 
   * return json
   */
  public function show($ticketType = 1)
  {
      // Get all categories for the given ticket type
      $categories = $this->cabinCategory
        ->with(['cabins' => function ($query) use ($ticketType) {
          $query->where('cabin_type_id', $ticketType);
        }])
        ->select('category_type', 'display_order', 'id', 'category_name', 'category_code')
        ->get();

      // Group categories by category type and get the first category of each type
      $groupedCategories = $categories->groupBy('category_type')->map(function ($group) {
        return $group->sortBy('display_order')->first();
      })->values()->sortBy('display_order');

      // Format categories for response
      $formattedCategories = $groupedCategories->map(function ($category) use ($categories, $ticketType) {
        return [
            'main_category' => [
                'name' => $category->category_type,
                'display_order' => $category->display_order,
                'max_capacity' => $category->category_type === 'Suite' ? 8 : 6,
                'categories' => $this->getCategories($category->category_type, $categories, $ticketType),
            ]
        ];
    });

    return response()->json($formattedCategories->values());
  }

  // Get categories based on category type
  public function getCategories($categoryType, $categories, $ticketType)
  {
    $filteredCategories = $categories->where('category_type', $categoryType)
        ->groupBy('category_name')
        ->map(function ($group) {
            return $group->sortBy('display_order')->first();
        })
        ->sortBy('display_order')
        ->values();

    return $filteredCategories->map(function ($category) use ($ticketType, $categoryType) {
      return [
        'name' => $category->category_name,
        'display_order' => $category->display_order,
        'cabins' => $this->getCabinsByCategory($categoryType, $category, $ticketType),
      ];
    });
  }

  // Get cabins based on category name
  public function getCabinsByCategory($categoryType, $category, $ticketType)
  {
    $categories = $this->cabinCategory
      ->where('category_type', $categoryType)
      ->where('category_name', $category->category_name)
      ->with(['cabins' => function ($query) use ($ticketType, $category) {
          $query->where('cabin_category_id', $category->id)
              ->where('cabin_type_id', $ticketType);
      }])
      ->get();


    $uniqueCodes = MatrixHelper::getUniqueCabinCodes($categories, $ticketType);
    

    if($uniqueCodes === null) {
      return [];
    }

    return collect($uniqueCodes)
        ->filter(function ($uniqueCode) {
            return $uniqueCode !== null;
        })
        ->map(function ($uniqueCode) use ($ticketType) {
            return [
                'category_id' => $uniqueCode['cabin_category_id'],
                'code' => $uniqueCode['code'],
                'decks' => $uniqueCode['decks'],
                'display_order' => $uniqueCode['display_order'],
                'price_and_availability' => $this->getPrices($uniqueCode['code'], $ticketType),
            ];
        })
        ->values();
  }


  // Get prices based on cabin category code and type
  public function getPrices($uniqueCode, $ticketType)
  {
    // Get all cabins for the given category code at once
    $cabins = $this->cabinCategory
        ->where('category_code', $uniqueCode)
        ->with(['cabins' => function ($query) use ($ticketType) {
            $query->where('cabin_type_id', $ticketType);
        }])
        ->get();

    // Early return if no cabins found
    if ($cabins->isEmpty()) {
        return [];
    }

    // Cache availability check to avoid repeated calls
    $availabilityStatus = MatrixHelper::checkCabinAvailability($cabins->first()->id, $ticketType);

    // Create price structure based on capacity
    $price = [
      "price_capacity_2" => MatrixHelper::getPriceDetails($cabins, 2, $availabilityStatus),
      "price_capacity_3" => MatrixHelper::getPriceDetails($cabins, 3, $availabilityStatus),
      "price_capacity_4" => MatrixHelper::getPriceDetails($cabins, 4, $availabilityStatus),
      "price_capacity_5" => MatrixHelper::getPriceDetails($cabins, 5, $availabilityStatus),
      "price_capacity_6" => MatrixHelper::getPriceDetails($cabins, 6, $availabilityStatus),
      "price_capacity_7" => MatrixHelper::getPriceDetails($cabins, 7, $availabilityStatus),
      "price_capacity_8" => MatrixHelper::getPriceDetails($cabins, 8, $availabilityStatus),
    ];

    return $price;
  }

}