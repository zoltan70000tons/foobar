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
   * default cabin id is 1, 2 is male, 3 is female
   * 
   * return json
   */
  public function show($ticketType = 1)
  {
    $uniqueCatTypes = $this->cabinCategory
      ->select('category_type')
      ->distinct()
      ->get();

    $formattedCategory = $uniqueCatTypes
      ->map(function ($category) use ($ticketType) {
        return [
          'main_category' => [
            'name' => $category->category_type,
            'categories' => $this->getCategories($category->category_type, $ticketType)
          ]
        ];
      });

    return response()->json($formattedCategory);
  }

  // Get categories based on category type
  public function getCategories($categoryType, $ticketType)
  {
    $cabinsGroups = $this->cabinCategory
      ->where('category_type', $categoryType)
      ->get();
    
    $categoryShortNames = MatrixHelper::getUniqueCabinNames($cabinsGroups->pluck('category_name')->toArray());
    $formattedCategories = [];
    
    if($categoryShortNames === null) {
      return [
        'name_short' => 'No category found',
        'cabins' => []
      ];
    }

    foreach ($categoryShortNames as $categoryShortName) {

      if($categoryShortName === null) {
        continue;
      }

      $formattedCategories[] = [
          'name_short' => $categoryShortName,
          'cabins' => $this->getCabinsByCategory($categoryType, $ticketType),
      ];
    }
    
    return $formattedCategories;
  }

  // Get cabins based on category name
  public function getCabinsByCategory($categoryType, $ticketType)
  {
    $cabinsGroups = $this->cabinCategory
      ->where('category_type', $categoryType)
      ->get();

    $uniqueCodes = MatrixHelper::getUniqueCabinCodes($cabinsGroups, $ticketType);
    $prices = [];

    if($uniqueCodes === null) {
      return [];
    }

    foreach ($uniqueCodes as $uniqueCode) {

      if($uniqueCode === null) {
        continue;
      }

      $prices[] = [
        'category_id' => $uniqueCode['cabin_category_id'],
        'code' => $uniqueCode['code'],
        'decks' => $uniqueCode['decks'],
        'price_and_availability' => $this->getPrices($uniqueCode['code'], $ticketType),
      ];
    }

    return $prices;
  }

  // Get prices based on cabin category code and type
  public function getPrices($uniqueCode, $ticketType)
  {
    // Get all cabins for the given category code at once
    $cabins = $this->cabinCategory
      ->where('category_code', $uniqueCode)
      ->get();

    // Cache availability check to avoid repeated calls
    $availabilityStatus = MatrixHelper::checkCabinAvailability($uniqueCode, $ticketType);

    // Create price structure based on capacity
    $price = [
      "price_capacity_2" => MatrixHelper::getPriceDetails($cabins, 2, $availabilityStatus),
      "price_capacity_3" => MatrixHelper::getPriceDetails($cabins, 3, $availabilityStatus),
      "price_capacity_4" => MatrixHelper::getPriceDetails($cabins, 4, $availabilityStatus),
      "price_capacity_5" => MatrixHelper::getPriceDetails($cabins, 5, $availabilityStatus),
      "price_capacity_6" => MatrixHelper::getPriceDetails($cabins, 6, $availabilityStatus),
    ];

    return $price;
  }

}
