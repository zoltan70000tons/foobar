<?php

namespace App\Http\Controllers\Api\Customer;

use App\Helpers\MatrixHelper;
use App\Http\Controllers\Controller;

use App\Models\Cabin;
use App\Models\CabinType;
use App\Models\CabinCategory;

use Illuminate\Support\Facades\Log;

class PricingMatrixController extends Controller
{

  protected $cabin;
  protected $cabinType;
  protected $cabinCategory;

  public function __construct(CabinType $cabinType, CabinCategory $cabinCategory, Cabin $cabin)
  {
    $this->cabin = $cabin;
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
  public function show($ticketType)
  {
      // Get all categories for the given ticket type
      $categories = $this->cabinCategory
        ->with(['cabins' => function ($query) use ($ticketType) {
          $query->where('cabin_type_id', $ticketType);
        }])
        ->select('category_type', 'display_order', 'id', 'category_name', 'category_code', 'capacity', 'price')
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

    // return unique category codes

    return $filteredCategories->map(function ($category) use ($categories, $ticketType) {
      return [
        'name' => $category->category_name,
        'cabin_category_id' => $category->id,
        'display_order' => $category->display_order,
        'cabins' => MatrixHelper::getUniqueCategories($categories, $category->category_name, $ticketType),
      ];
    });
  }

}