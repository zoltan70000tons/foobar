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
    public function show($eventId, $cabinTypeId)
    {
        return $this->buildPricingMatrix($eventId, $cabinTypeId);
    }

    public function show2($eventId, $cabinTypeId)
    {
        return $this->buildPricingMatrix($eventId, $cabinTypeId);
    }

    protected function buildPricingMatrix($eventId, $cabinTypeId)
    {
        if (!$eventId || !$cabinTypeId) {
            return response()->json(['message' => 'Event ID and Cabin Type ID are required'], 400);
        }

        $categories = CabinCategory::where('event_id', $eventId)
            ->with([
                'cabins' => function ($q) use ($cabinTypeId) {
                    $q->with('cabinSpec');
                },
                'spec'
            ])
            ->get();

        if (!$categories->count()) {
            return response()->json(['message' => 'No categories found'], 404);
        }

        // Cabin numbers reserved for this cabin type (or all)
        $reservationsCabinNumbers = TemporaryReservation::when($cabinTypeId, function ($q) use ($cabinTypeId) {
            $q->whereHas('cabin', fn($c) => $c->where('cabin_type_id', $cabinTypeId));
        })
            ->pluck('cabin_number')
            ->toArray();

        // Pick representative categories per type
        $groupedCategories = $categories
            ->groupBy('category_type')
            ->map(fn($group) => $group->sortBy('displayOrder')->first())
            ->sortBy('displayOrder');

        // Map formatting (either variant 1 or 2 logic)
        $formatted = $groupedCategories->map(function ($category) use ($categories, $cabinTypeId, $reservationsCabinNumbers) {
            return $this->formatCategoryUnified($category, $categories, $cabinTypeId, $reservationsCabinNumbers);
        });

        return response()->json($formatted->values());
    }

    protected function formatCategoryUnified($category, $categories, $cabinTypeId, $reservationsCabinNumbers)
    {
        $spec = $category->spec;

        // Max capacity logic
        $maxCapacity = $cabinTypeId
            ? ($cabinTypeId !== '1'
                ? 4
                : ($spec->category_type === 'Suite' ? 8 : 6))
            : ($spec->category_type === 'Suite' ? 8 : 6);

        return [
            'main_category' => [
                'name'          => $spec->category_type,
                'display_order' => $spec->display_order,
                'max_capacity'  => $maxCapacity,
                'categories'    => $this->getCategoriesUnified(
                    $spec->category_type,
                    $categories,
                    $cabinTypeId,
                    $reservationsCabinNumbers
                ),
            ],
        ];
    }

    public function getCategoriesUnified(
        $categoryType,
        $categories,
        $cabinTypeId,
        $reservationsCabinNumbers
    ) {
        return $categories
            ->where('category_type', $categoryType)
            ->sortBy('display_order')
            ->unique('category_name')
            ->map(function ($category) use ($categories, $cabinTypeId, $reservationsCabinNumbers) {
                return $this->formatFilteredCategoryUnified(
                    $category,
                    $categories,
                    $cabinTypeId,
                    $reservationsCabinNumbers
                );
            })
            ->values();
    }

    protected function formatFilteredCategoryUnified(
        $category,
        $categories,
        $cabinTypeId,
        $reservationsCabinNumbers
    ) {
        return [
            'name'             => $category->category_name,
            'cabin_category_id'=> $category->id,
            'display_order'    => $category->display_order,
            'cabins'           => MatrixHelper::getUniqueCategoriesUnified(
                $categories,
                $category->category_name,
                $cabinTypeId,
                $reservationsCabinNumbers
            ),
        ];
    }
}
