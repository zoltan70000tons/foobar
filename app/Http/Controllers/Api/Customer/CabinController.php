<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CabinCategory;
use App\Models\CabinType;
use App\Models\TemporaryReservation;
use App\Traits\CabinFilter;
use Illuminate\Support\Facades\DB;
use App\Services\ReservationService;
use Log;

class CabinController extends Controller
{
  use CabinFilter;

  /**
   * Show cabins filtered by type, category, and deck.
   */
  public function show($cabinTypeId, $cabinCategoryId, $cabinDeck)
  {
    $filteredCabins = $this->filterCabins($cabinTypeId, $cabinCategoryId, $cabinDeck, false);

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

  /*
  |--------------------------------------------------------------------------
  | Reserve a specific cabin for the user.
  |--------------------------------------------------------------------------
  |
  |  User select a specific cabin.
  |
  |  If user have a reservation, and he want to create new.
  |  Release the current cabin but keep reservation_timestamp in session.
  |
  */
  public function reserveCabinInType(Request $request, ReservationService $reservationService)
  {
    // REQUEST INPUT DATA
    $cabinNumber = $request->input('cabin_number');
    $cabinTypeId = $request->input('cabin_type_id');
    $cabinCategoryId = $request->input('cabin_category_id');

    $cart = $request->session()->get('cart', []) ?? null;
    $reservationId = $cart['reservation_id'] ?? null;
    $keepOldTimeStamp = null;

    // ------- If user have a reservation, and he want to create new.
    if ($cart && $reservationId) {
      // get from Temporary reservation table, and set expires_at to keepOldTimeStamp
      $keepOldTimeStamp = TemporaryReservation::where('id', $reservationId)->value('expires_at');

      Log::info('Keep old timestamp', ['keepOldTimeStamp' => $keepOldTimeStamp]);

      // release the current reservation
      $reservationService->releaseCabin($request);

      // remove cabin_number from session, because if something went wrong.
      // we don't want to keep the old cabin_number in session to prevent booking.
      $request->session()->put('cart.cabin_number', null);
      $request->session()->put('cart.reservation_id', null);
      $request->session()->put('cart.reservation_timestamp', null);
    }

    // -------- Proceed with new reservation
    if (!$cabinNumber || !$cabinTypeId || !$cabinCategoryId) {
      return response()->json(['message' => 'Cabin number, type ID, and category ID are required.'], 400);
    }

    $filteredCabins = $this->filterCabins($cabinTypeId, $cabinCategoryId);

    if (isset($filteredCabins['error'])) {
      return response()->json(['message' => $filteredCabins['error']], $filteredCabins['status']);
    }

    $cabin = collect($filteredCabins['cabins'])->firstWhere('cabin_number', $cabinNumber);

    if (!$cabin) {
      return response()->json(['message' => 'Cabin not found or may be reserved'], 404);
    }

    return $this->createTemporaryReservation($cabin, $request, 'clientSelect', $keepOldTimeStamp);
  }

  /*
  |--------------------------------------------------------------------------
  | Reserve a cabin type
  |--------------------------------------------------------------------------
  |
  |  Reserve a cabin type byy customer service.
  |  User did not choose a cabin, so we will assign one.
  |
  */
  public function reserveType(Request $request)
  {
    // $reservationTime = (int) env('TEMPORARY_RESERVATION_TIME', 6);

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

    $cabin = collect($filteredCabins['cabins'])->first();

    if (!$cabin) {
      return response()->json(['message' => 'No available cabins found'], 404);
    }

    return $this->createTemporaryReservation($cabin, $request, false);
  }

  /*
  |--------------------------------------------------------------------------
  | Release cabin
  |--------------------------------------------------------------------------
  |
  |  Relase logic as service, to use as separate endpoint or in CartController
  |
  */
  public function release(Request $request, ReservationService $reservationService)
  {
    $response = $reservationService->releaseCabin($request);

    return response()->json(['message' => $response['message']], $response['status']);
  }

  /**
   * Helper method to create a temporary reservation.
   */
  protected function createTemporaryReservation($cabin, Request $request, $selectionType, $keepOldTimeStamp = null)
  {
    $reservationTime = (int) env('TEMPORARY_RESERVATION_TIME');

    Log::info('Creating temporary reservation', [
      'cabin' => $cabin,
      'selectionType' => $selectionType,
      'keepOldTimeStamp' => $keepOldTimeStamp,
    ]);

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
        'expires_at' => $keepOldTimeStamp ? $keepOldTimeStamp : now()->addMinutes($reservationTime),
        'inventory' => 1,
      ]);

      $request->session()->put('reserved_cabin_id', $reserved->id);

      $prevTimestamp = $keepOldTimeStamp ? $request->session()->get('cart.reservationTimestamp') : null;

      $request->session()->put('cart', [
        'cabinSelection' => $selectionType,
        'reservationId' => $reserved->id,
        'cabin_number' => $cabin['cabin_number'],
        'cabin_category_type' => $cabin['cabin_category_type'] ?? null,
        'reservationTimestamp' => $prevTimestamp ? $prevTimestamp : now()->timestamp,
      ]);

      DB::commit();

      return response()->json(
        [
          'success' => true,
          'message' => 'Cabin reserved',
          'reservation_id' => $reserved->id,
          'time_to_cancel' => $reservationTime,
          'updated_reservation' => $keepOldTimeStamp ? true : false,
          'cabin_number' => $selectionType === 'clientSelect' ? $reserved->cabin_number : null,
        ],
        200
      );
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json(['message' => 'Reservation failed'], 500);
    }
  }
}
