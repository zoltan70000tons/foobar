<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CabinCategory;
use App\Models\CabinType;
use App\Models\TemporaryReservation;
use App\Traits\CabinFilter;
use Illuminate\Support\Facades\DB;

class CabinController extends Controller
{
  use CabinFilter;

  /**
   * Show cabins filtered by type, category, and deck.
   */
  public function show($cabinTypeId, $cabinCategoryId, $cabinDeck)
  {
    $filteredCabins = $this->filterCabins($cabinTypeId, $cabinCategoryId, $cabinDeck);

    if (isset($filteredCabins['error'])) {
      return response()->json(['message' => $filteredCabins['error']], $filteredCabins['status']);
    }

    return response()->json($filteredCabins['cabins'], 200);
  }

  /**
   * Show a single cabin category by ID.
   */
  public function showCategory($categoryId)
  {
    // Eager-load spec for static attributes
    $category = CabinCategory::with('spec')->find($categoryId);

    if (!$category) {
      return response()->json(['message' => 'Category not found'], 404);
    }

    return response()->json($category);
  }

  /**
   * Show all cabin types.
   */
  public function showTypes()
  {
    $cabinTypes = CabinType::select('id', 'cabin_type')->get();

    if ($cabinTypes->isEmpty()) {
      return response()->json(['message' => 'No cabin types found'], 404);
    }

    return response()->json($cabinTypes);
  }

  /**
   * Reserve a specific cabin for the user.
   */
  public function reserveCabinInType(Request $request)
  {
    if ($request->session()->has('reserved_cabin_id')) {
      return response()->json(['message' => 'You have already reserved a cabin.'], 403);
    }

    $cabinNumber = $request->input('cabin_number');
    $cabinTypeId = $request->input('cabin_type_id');
    $cabinCategoryId = $request->input('cabin_category_id');

    if (!$cabinNumber || !$cabinTypeId || !$cabinCategoryId) {
      return response()->json(['message' => 'Cabin number, type ID, and category ID are required.'], 400);
    }

    $filteredCabins = $this->filterCabins($cabinTypeId, $cabinCategoryId);

    if (isset($filteredCabins['error'])) {
      return response()->json(['message' => $filteredCabins['error']], $filteredCabins['status']);
    }

    $cabin = $filteredCabins['cabins']->firstWhere('cabin_number', $cabinNumber);

    if (!$cabin) {
      return response()->json(['message' => 'Cabin not found or may be reserved'], 404);
    }

    return $this->createTemporaryReservation($cabin, $request, 'clientSelect');
  }

  /**
   * Reserve a cabin type for customer service.
   */
  public function reserveType(Request $request)
  {
    $reservationTime = (int) env('TEMPORARY_RESERVATION_TIME', 6);

    if ($request->session()->has('reserved_cabin_id')) {
      return response()->json(['message' => 'You have already reserved a cabin.'], 403);
    }

    $cabinTypeId = $request->input('cabin_type_id');
    $cabinCategoryId = $request->input('cabin_category_id');

    if (!$cabinTypeId || !$cabinCategoryId) {
      return response()->json(['message' => 'Cabin type and category are required.'], 400);
    }

    $filteredCabins = $this->filterCabins($cabinTypeId, $cabinCategoryId);

    if (isset($filteredCabins['error'])) {
      return response()->json(['message' => $filteredCabins['error']], $filteredCabins['status']);
    }

    $cabin = $filteredCabins['cabins']->first();

    if (!$cabin) {
      return response()->json(['message' => 'No available cabins found'], 404);
    }

    return $this->createTemporaryReservation($cabin, $request, false, $reservationTime);
  }

  /**
   * Release the currently reserved cabin.
   */
  public function release(Request $request)
  {
    if (!$request->session()->has('reserved_cabin_id')) {
      return response()->json(['message' => 'No cabin reserved'], 404);
    }

    $reservationId = $request->session()->get('reserved_cabin_id');
    $reservation = TemporaryReservation::find($reservationId);

    if (!$reservation) {
      return response()->json(['message' => 'No reservation found'], 404);
    }

    if ($request->user() && $reservation->user_id !== $request->user()->id) {
      return response()->json(['message' => 'Unauthorized'], 403);
    }

    $reservation->delete();
    $request->session()->forget(['reserved_cabin_id', 'cart']);

    return response()->json(['message' => 'Cabin released'], 200);
  }

  /**
   * Helper method to create a temporary reservation.
   */
  protected function createTemporaryReservation($cabin, Request $request, $selectionType, $reservationTime = 5)
  {
    try {
      DB::beginTransaction();

      $existingReservation = TemporaryReservation::where('cabin_id', $cabin['id'])
        ->where('expires_at', '>', now())
        ->lockForUpdate()
        ->first();

      if ($existingReservation) {
        DB::rollBack();
        return response()->json(['message' => 'Cabin already reserved'], 404);
      }

      $reserved = TemporaryReservation::create([
        'user_id' => $request->user()->id ?? null,
        'cabin_id' => $cabin['id'],
        'cabin_number' => $cabin['cabin_number'],
        'expires_at' => now()->addMinutes($reservationTime),
        'inventory' => 1,
      ]);

      $request->session()->put('reserved_cabin_id', $reserved->id);

      $request->session()->put('cart', [
        'cabinSelection' => $selectionType,
        'reservationId' => $reserved->id,
        'cabin_number' => $cabin['cabin_number'],
        'cabin_category_type' => $cabin['cabin_category_type'] ?? null,
        'reservationTimestamp' => now()->timestamp,
      ]);

      DB::commit();

      return response()->json([
        'success' => true,
        'message' => 'Cabin reserved',
        'reservation_id' => $reserved->id,
      ], 200);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json(['message' => 'Reservation failed'], 500);
    }
  }
}
