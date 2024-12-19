<?php

namespace App\Http\Controllers\Api\Customer;

use App\Helpers\MatrixHelper;
use App\Http\Controllers\Controller;
use App\Models\CabinCategory;
use App\Models\CabinType;

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
  public function index()
  {
    $cabinTypes = CabinType::select("id", "cabin_type")->get();
    return response()->json($cabinTypes);
  }

  /**
   * Show cabins grouped by ticket type.
   */
  public function show($ticketType)
  {
    // Fetch categories with cabins and specs based on ticket type
    $categories = CabinCategory::with([
      'cabins' => fn($query) => $query->where('cabin_type_id', $ticketType),
      'spec',
    ])->get();

    // Group categories by type, sort, and select the first for each type
    $groupedCategories = $categories
      ->groupBy(fn($category) => $category->category_type)
      ->map(fn($group) => $group->sortBy(fn($category) => $category->displayOrder)->first())
      ->sortBy(fn($category) => $category->displayOrder);

    // Format categories for response
    $formattedCategories = $groupedCategories->map(fn($category) => $this->formatCategory($category, $categories, $ticketType));

    return response()->json($formattedCategories->values());
  }

  /**
   * Format a category for response.
   */
  protected function formatCategory($category, $categories, $ticketType)
  {
    $categorySpec = $category->spec;

    // Determine max capacity
    $maxCapacity = $ticketType !== "1"
      ? 4 // Single Ticket max capacity
      : ($categorySpec->category_type === "Suite" ? 8 : 6); // Private Cabin max capacity

    return [
      "main_category" => [
        "name" => $categorySpec->category_type,
        "display_order" => $categorySpec->display_order,
        "max_capacity" => $maxCapacity,
        "categories" => $this->getCategories($categorySpec->category_type, $categories, $ticketType),
      ],
    ];
  }

  /**
   * Get categories based on category type.
   */
  public function getCategories($category_type, $categories, $ticketType)
  {
    // Filter, group, and sort categories by category type
    $filteredCategories = $categories
      ->filter(fn($category) => $category->category_type === $category_type)
      ->groupBy(fn($category) => $category->category_name)
      ->map(fn($group) => $group->sortBy(fn($category) => $category->display_order)->first())
      ->sortBy(fn($category) => $category->display_order)
      ->values();

    // Map categories for response
    return $filteredCategories->map(fn($category) => $this->formatFilteredCategory($category, $categories, $ticketType));
  }

  /**
   * Format a filtered category for response.
   */
  protected function formatFilteredCategory($category, $categories, $ticketType)
  {
    return [
      "name" => $category->category_name,
      "cabin_category_id" => $category->id,
      "display_order" => $category->display_order,
      "cabins" => MatrixHelper::getUniqueCategories($categories, $category->category_name, $ticketType),
    ];
  }
}
