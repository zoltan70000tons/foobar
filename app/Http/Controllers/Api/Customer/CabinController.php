<?php

namespace App\Http\Controllers\Api\Customer;

use App;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CabinCategory;
use App\Models\TemporaryReservation;
use App\Traits\CabinFilter;
use Illuminate\Support\Facades\DB;
use App\Services\ReservationService;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use Illuminate\Support\Facades\Cache;
use Log;

class CabinController extends Controller
{
  use CabinFilter;

  /**
   * Show cabins filtered by type, category, and deck.
   */
  public function show($cabinTypeId, $cabinCategoryCode, $cabinCapacity, $cabinDeck)
  {
    // Convert parameters to the correct type
    $cabinTypeId = intval($cabinTypeId);
    if ($cabinTypeId !== 1) {
      return response()->json(
        [
          'message' => 'Option avaialble only for private cabins.',
        ],
        404
      );
    }

    $cabinCapacity = $cabinCapacity !== null ? intval($cabinCapacity) : null;
    $cabinDeck = $cabinDeck !== null ? intval($cabinDeck) : null;

    $filteredCabins = $this->filterCabins($cabinTypeId, null, $cabinDeck, false, $cabinCategoryCode, $cabinCapacity);

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

  /*
  |--------------------------------------------------------------------------
  | Show cabin types
  |--------------------------------------------------------------------------
  |
  |  We have in db only 3 types of cabins.
  |  So we dont need to use eloquent model for this. and we can cache it.
  |
  */
  public function showTypes()
  {
    // $cabinTypes = CabinType::select('id', 'cabin_type')->get();
    $cabinTypes = Cache::remember('cabin_types', now()->addHours(12), function () {
      return DB::table('cabin_types')->select('id', 'cabin_type')->get();
    });

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
  |  ONLY FOR PRIVATE CABINS !!!
  |
  |  User select a specific cabin.
  |
  |  If user have a reservation, and he want to create new.
  |  Release the current cabin but keep reservation_timestamp in session.
  |
  */
  public function reserveCabinInType(Request $request, ReservationService $reservationService)
  {
    $user = Auth::user();
    $language = $request->input('language', 'en');
    App::setLocale($language);

    // REQUEST INPUT DATA
    $cabinNumber = $request->input('cabin_number');
    $cabinTypeId = $request->input('cabin_type_id');
    $cabinCapacity = $request->input('cabin_capacity');
    $cabinCategoryCode = $request->input('category_code');

    $cart = $user ? Cart::where('user_id', $user->id)->first()?->cart_data ?? [] : [];

    $reservationId = $cart['reservation_id'] ?? null;
    $keepOldTimeStamp = null;

    // if cabin type id is not 1, return error
    if ($cabinTypeId !== 1) {
      return response()->json(['message' => 'Option available only for private cabin'], 400);
    }

    // ------- If user have a reservation, and he want to create new.
    if ($cart && $reservationId) {
      // get from Temporary reservation table, and set expires_at to keepOldTimeStamp
      $keepOldTimeStamp = TemporaryReservation::where('id', $reservationId)->value('expires_at');

      // release the current reservation
      $reservationService->releaseCabin($request);

      $cart['cabin_number'] = null;
      $cart['reservation_id'] = null;
      $cart['reservationTimestamp'] = null;
      Cart::updateOrCreate(['user_id' => $user->id], ['cart_data' => $cart]);
    }

    // ------- If user id is in tepmorary reservation table, thriow error

    $tempReservationId = TemporaryReservation::where('user_id', $user->id)->get();

    // if user have a reservation throw error
    if ($tempReservationId->isNotEmpty()) {
      return response()->json(['message' => __('feedback.double_booking')], 403);
    }

    // TemporaryReservation::where('user_id', $user->id)->delete();

    // -------- Proceed with new reservation
    if (!$cabinNumber || !$cabinTypeId || !$cabinCategoryCode || !$cabinCapacity) {
      return response()->json(['message' => 'Cabin number, type ID, capacity, and category ID are required.'], 400);
    }

    $filteredCabins = $this->filterCabins($cabinTypeId, null, null, false, $cabinCategoryCode, $cabinCapacity);

    if (isset($filteredCabins['error'])) {
      return response()->json(['message' => $filteredCabins['error']], $filteredCabins['status']);
    }

    $cabin = collect($filteredCabins['cabins'])->firstWhere('cabin_number', $cabinNumber);

    if (!$cabin) {
      return response()->json(['message' => __('feedback.cabin_not_available')], 404);
    }

    return $this->createTemporaryReservation($cabin, $request, 'clientSelect', $keepOldTimeStamp);
  }

  /*
  |--------------------------------------------------------------------------
  | Reserve a cabin type
  |--------------------------------------------------------------------------
  |
  |  Reserve a cabin type by customer service.
  |  User did not choose a cabin, so we will assign one.
  |
  */
  public function reserveType(Request $request)
  {
    // $reservationTime = (int) env('TEMPORARY_RESERVATION_TIME', 6);

    if ($request->session()->has('reserved_cabin_id')) {
      return response()->json(['message' => __('feedback.double_booking')], 403);
    }

    $cabinTypeId = $request->input('cabin_type_id');
    $cabinCategoryCode = $request->input('category_code');
    $cabinCapacity = $request->input('capacity');

    $user = Auth::user();

    if (!$user) {
      return response()->json(['message' => 'User not authenticated'], 401);
    }

    // ------- If user id is in tepmorary reservation table, thriow error
    $tempReservationId = TemporaryReservation::where('user_id', $user->id)->get();

    // if user have a reservation throw error
    if ($tempReservationId->isNotEmpty()) {
      TemporaryReservation::where('user_id', $user->id)->delete();
      return response()->json(['message' => __('feedback.double_booking')], 403);
    }

    if (!$cabinTypeId || !$cabinCategoryCode || !$cabinCapacity) {
      return response()->json(['message' => 'Cabin category code, capacity and cabin type are required.'], 400);
    }

    $filteredCabins = $this->filterCabins($cabinTypeId, null, null, true, $cabinCategoryCode, $cabinCapacity);

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
    $user = Auth::user();

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

      // if (!$user) {
      //   $request->session()->put('cart', [
      //     'cabinSelection' => $selectionType,
      //     'reservationId' => $reserved->id,
      //     'cabin_number' => $cabin['cabin_number'],
      //     'cabin_category_type' => $cabin['cabin_category_type'] ?? null,
      //     'reservationTimestamp' => $prevTimestamp ? $prevTimestamp : now()->timestamp,
      //     'lower_bed_type_2' => $cabin['lower_bed_type_2'],
      //   ]);
      // } else {
      //   $cart = $user->cart_data;

      //   $cart['cabinSelection'] = $selectionType;
      //   $cart['reservationId'] = $reserved->id;
      //   $cart['cabin_number'] = $cabin['cabin_number'];
      //   $cart['cabin_category_type'] = $cabin['cabin_category_type'] ?? null;
      //   $cart['reservationTimestamp'] = $prevTimestamp ? $prevTimestamp : now()->timestamp;
      //   $cart['lower_bed_type_2'] = $cabin['lower_bed_type_2'];

      //   Cart::updateOrCreate(['user_id' => $user->id], ['cart_data' => $cart]);
      // }

      $cart = $user->cart_data;

      $cart['cabinSelection'] = $selectionType;
      $cart['reservationId'] = $reserved->id;
      $cart['cabin_number'] = $cabin['cabin_number'];
      $cart['cabin_category_type'] = $cabin['cabin_category_type'] ?? null;
      $cart['reservationTimestamp'] = $prevTimestamp ? $prevTimestamp : now()->timestamp;
      $cart['lower_bed_type_2'] = $cabin['lower_bed_type_2'];

      Cart::updateOrCreate(['user_id' => $user->id], ['cart_data' => $cart]);

      DB::commit();

      return response()->json(
        [
          'success' => true,
          'message' => 'Cabin reserved',
          'reservation_id' => $reserved->id,
          'time_to_cancel' => $reservationTime,
          'updated_reservation' => $keepOldTimeStamp ? true : false,
          'cabin_number' => $selectionType === 'clientSelect' ? $reserved->cabin_number : null,
          'lower_bed_type_2' => $cabin['lower_bed_type_2'],
        ],
        200
      );
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json(['message' => 'Reservation failed'], 500);
    }
  }
}
