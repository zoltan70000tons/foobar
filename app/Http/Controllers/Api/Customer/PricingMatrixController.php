<?php

namespace App\Http\Controllers\Api\Customer;

use App\Helpers\MatrixHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CabinType;
use App\Models\Cabin;
use App\Models\CabinCategory;


class PricingMatrixController extends Controller
{

  protected $cabinType;
  protected $cabinCategory;
  protected $cabin;

  public function __construct(CabinType $cabinType, CabinCategory $cabinCategory, Cabin $cabin)
  {
    $this->cabinType = $cabinType;
    $this->cabinCategory = $cabinCategory;
    $this->cabin = $cabin;
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

  /***
   * Show private cabin
   * 
   * return json
   * 
   */
  public function show($cabinId)
  {

    $uniqueCatTypes = $this->cabinCategory
      ->select('*') // Select all fields
      ->groupBy('category_type') // Group by category_type to get unique ones
      ->get();

    // here we got main category like Balocony, Interior, Ocean View
    $formattedCategory = $uniqueCatTypes
      ->map(function ($category) use ($cabinId) {
        return [
          'main_category' => [
            'name' => $category->category_type,
            'categories' => $this->getCategories($category->category_type, $cabinId)
          ]
        ];
      });

    return response()->json($formattedCategory);
  }

  // get categories based on category type
  public function getCategories($categoryType, $cabinTypeId)
  {
    $uniqueCategory = $this->cabinCategory
      ->where('category_type', $categoryType)
      ->groupBy('category_name')
      ->get()
      ->map(function ($item) use ($cabinTypeId) {

        $deck = MatrixHelper::getDecks($cabinTypeId, $item->id);

        return [
          'category_name' => $item->category_name,
          'category_capacity' => $item->capacity,
          'display_order' => $item->display_order,
          'short_name' => MatrixHelper::getNameBeforeFirstNumber($item->category_name, $cabinTypeId),
          'related_category_code' => [
            'code' => $item->category_code,
            'decks' => $deck,
            'related_prices' => $deck ? $this->getPrices($item, $cabinTypeId) : null
          ],
        ];
      });

    return $uniqueCategory;
  }

  // get prices
  public function getPrices($item, $cabinTypeId)
  {
    $prices = [
      'price' => $item->price,
      'isAvailable' => MatrixHelper::checkCabinAvailability($cabinTypeId, $item->id),
    ];

    return $prices;
  }
}
