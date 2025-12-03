<?php

namespace App\Http\Controllers\Api\Customer;

use App\Helpers\MatrixHelper;
use App\Http\Controllers\Controller;
use App\Models\CabinCategory;
use App\Models\CabinType;
use Illuminate\Support\Facades\Concurrency;
use App\Enums\StatusCabin;
use App\Models\TemporaryReservation;


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
   * Show cabins grouped by ticket type.
   */
  public function show2($eventId, $cabinTypeId)
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
    
    $reservationsCabinNumbers = TemporaryReservation::whereHas('cabin', function ($query) use ($cabinTypeId) {
      $query->where('cabin_type_id', $cabinTypeId);
    })
      ->pluck('cabin_number')
      ->toArray();
    

    // Group categories by type, sort, and select the first for each type
    $groupedCategories = $categories
      ->groupBy(fn($category) => $category->category_type)
      ->map(fn($group) => $group->sortBy(fn($category) => $category->displayOrder)->first())
      ->sortBy(fn($category) => $category->displayOrder);

    // Format categories for response
    $formattedCategories = $groupedCategories->map(
      fn($category) => $this->formatCategory($category, $categories, $cabinTypeId, $reservationsCabinNumbers)
    );

    return response()->json($formattedCategories->values());
  }

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

        $reservationsCabinNumbers = TemporaryReservation::whereHas('cabin', function ($query) use ($cabinTypeId) {
            $query->where('cabin_type_id', $cabinTypeId);
        })
            ->pluck('cabin_number')
            ->toArray();


        // Group categories by type, sort, and select the first for each type
        $groupedCategories = $categories
            ->groupBy(fn($category) => $category->category_type)
            ->map(fn($group) => $group->sortBy(fn($category) => $category->displayOrder)->first())
            ->sortBy(fn($category) => $category->displayOrder);

        // Format categories for response
        $formattedCategories = $groupedCategories->map(
            fn($category) => $this->formatCategory($category, $categories, $cabinTypeId, $reservationsCabinNumbers)
        );

        return response()->json($formattedCategories->values());
    }

  /**
   * Format a category for response.
   */
  protected function formatCategory($category, $categories, $cabinTypeId, $reservationsCabinNumbers)
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
        'categories' => $this->getCategories($categorySpec->category_type, $categories, $cabinTypeId, $reservationsCabinNumbers),
      ],
    ];
  }

  /**
   * Get categories based on category type.
   */
  public function getCategories($categoryType, $categories, $cabinTypeId, $reservationsCabinNumbers)
  {
    // Filter, group, and sort categories by category type
    return $categories
      ->where('category_type', $categoryType)
      ->sortBy('display_order')
      ->unique('category_name')
      ->map(fn($category) => $this->formatFilteredCategory($category, $categories, $cabinTypeId, $reservationsCabinNumbers))
      ->values(); // Reset collection keys
  }

  /**
   * Format a filtered category for response.
   */
  protected function formatFilteredCategory($category, $categories, $cabinTypeId, $reservationsCabinNumbers)
  {
    return [
      'name' => $category->category_name,
      'cabin_category_id' => $category->id,
      'display_order' => $category->display_order,
      'cabins' => MatrixHelper::getUniqueCategories($categories, $category->category_name, $cabinTypeId, $reservationsCabinNumbers),
    ];
  }
}
