<?php

namespace App\Http\Controllers\Api\Customer;

use App\Helpers\MatrixHelper;
use App\Http\Controllers\Controller;
use App\Models\CabinCategory;
use App\Models\CabinType;
use Illuminate\Support\Facades\Concurrency;

class PricingMatrixController extends Controller
{
  protected $cabinType;
  protected $cabinCategory;

  /**
   * Constructor to inject dependencies.
   */
  public function __construct(CabinType $cabinType, CabinCategory $cabinCategory)
  {
    $this->cabinType = $cabinType;
    $this->cabinCategory = $cabinCategory;
  }

  /**
   * Index method: return name and id of cabin types.
   */
  // public function index()
  // {
  //   $cabinTypes = CabinType::select('id', 'cabin_type')->get();
  //   return response()->json($cabinTypes);
  // }

  /**
   * Show cabins grouped by ticket type.
   */
  public function show($eventId, $cabinTypeId)
  {
    if (!$eventId || !$cabinTypeId) {
      return response()->json(['message' => 'Event ID and Cabin Type ID are required'], 400);
    }

    // \DB::enableQueryLog();
    // Fetch categories with cabins and specs based on ticket type
    $categories = CabinCategory::where('event_id', $eventId)
      ->with(['cabins' => fn($query) => $query->where('cabin_type_id', $cabinTypeId)->with('cabinSpec'), 'spec'])
      ->get();

    if (!$categories->count()) {
      return response()->json(['message' => 'No categories found'], 404);
    }

    // Group categories by type, sort, and select the first for each type
    $groupedCategories = $categories
      ->groupBy(fn($category) => $category->category_type)
      ->map(fn($group) => $group->sortBy(fn($category) => $category->displayOrder)->first())
      ->sortBy(fn($category) => $category->displayOrder);

    // Format categories for response
    $formattedCategories = $groupedCategories->map(
      fn($category) => $this->formatCategory($category, $categories, $cabinTypeId)
    );

    return response()->json($formattedCategories->values());
  }

  /**
   * Format a category for response.
   */
  protected function formatCategory($category, $categories, $cabinTypeId)
  {
    $categorySpec = $category->spec;

    // Determine max capacity
    $maxCapacity =
      $cabinTypeId !== '1'
        ? 4 // Single Ticket max capacity
        : ($categorySpec->category_type === 'Suite'
          ? 8
          : 6); // Private Cabin max capacity

    return [
      'main_category' => [
        'name' => $categorySpec->category_type,
        'display_order' => $categorySpec->display_order,
        'max_capacity' => $maxCapacity,
        'categories' => $this->getCategories($categorySpec->category_type, $categories, $cabinTypeId),
      ],
    ];
  }

  /**
   * Get categories based on category type.
   */
  public function getCategories($categoryType, $categories, $cabinTypeId)
  {
    // \Log::info('Category type: ' . $category_type);

    // \Log::info('categoriescategories: ', ['categories' => $categories]);

    // // Debug original data
    // \Log::info('All categories:', [
    //   'count' => $categories->count(),
    //   'data' => $categories->map(
    //     fn($c) => [
    //       'id' => $c->id,
    //       'category_type' => $c->category_type,
    //       'category_name' => $c->category_name,
    //     ]
    //   ),
    // ]);

    // // Debug after first filter
    // $afterFirstFilter = $categories->filter(fn($category) => $category->category_type === $category_type);
    // \Log::info('After category_type filter:', [
    //   'count' => $afterFirstFilter->count(),
    //   'category_type' => $category_type,
    //   'data' => $afterFirstFilter->map(
    //     fn($c) => [
    //       'id' => $c->id,
    //       'category_type' => $c->category_type,
    //       'category_name' => $c->category_name,
    //     ]
    //   ),
    // ]);

    // Filter, group, and sort categories by category type
    return $categories
      ->where('category_type', $categoryType)
      ->sortBy('display_order')
      ->unique('category_name')
      ->map(fn($category) => $this->formatFilteredCategory($category, $categories, $cabinTypeId))
      ->values(); // Reset collection keys
  }

  /**
   * Format a filtered category for response.
   */
  protected function formatFilteredCategory($category, $categories, $cabinTypeId)
  {
    return [
      'name' => $category->category_name,
      'cabin_category_id' => $category->id,
      'display_order' => $category->display_order,
      'cabins' => MatrixHelper::getUniqueCategories($categories, $category->category_name, $cabinTypeId),
    ];
  }
}
