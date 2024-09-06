<?php

namespace App\Http\Controllers\Api\Customer;

use App\Helpers\MatrixHelper; // Add this line to import MatrixHelper
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

  public function showPrivateCabin()
  {

    // TODO: ADD THE LOGIC TO CHECK THE ID OF REQUEST RETURN PRIVATE CABIN

    // Fetch unique category types
    $uniqueCatTypes = $this->cabinCategory
      ->select('*') // Select all fields
      ->groupBy('category_type') // Group by category_type to get unique ones
      ->get();


    // Fetch unique category code where for each of categoryName
    $formattedCats = $uniqueCatTypes->map(function ($category) {

      $uniqueCategoryCodes = $this->cabinCategory
        ->where('category_type', $category->category_type)
        ->get()
        ->map(function ($item) {
          return [
            'parent_category_type' => $item->category_type,
            'sub_cat_id' => $item->id,
            'sub_cat_available' => MatrixHelper::checkCabinAvailability($item->id),
            'sub_cat_decks' => MatrixHelper::getDecks($item->id),
            'sub_cat_name' => $item->category_name,
            'sub_cat_short_name' => MatrixHelper::getNameBeforeFirstNumber($item->category_name),
            'sub_cat_code' => $item->category_code,
            'sub_cat_capacity' => $item->capacity,
            'sub_cat_description' => $item->description,
            'sub_cat_iframe' => $item->iframe,
            'sub_cat_images' => $item->images,
            'sub_cat_price' => $item->price,
            'sub_cat_display_order' => $item->display_order,
            'sub_cat_cruise_id' => $item->cruise_id,
            'sub_cat_event_id' => $item->event_id,
          ];
        })
        ->values();

      return [
        'main_category' => [
          'cat_id' => $category->id,
          'name' => $category->category_type,
          'sub_category' => [
            $uniqueCategoryCodes
          ],
        ]
      ];
    });

    return response()->json($formattedCats);
  }
}
