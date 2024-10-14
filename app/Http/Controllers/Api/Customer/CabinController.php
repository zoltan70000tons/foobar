<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cabin;
use App\Models\CabinType;
use App\Models\CabinCategory;
use App\Events\CabinChange;
use Illuminate\Support\Facades\Log;
use App\Enums\StatusCabin;
use Illuminate\Support\Carbon;
use App\Models\TemporaryReservation;
use App\Traits\CabinFilter;


class CabinController extends Controller
{
  use CabinFilter;

  
  /**
   * ----- SHOW CABIN -----
   * 
   * 
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
   * ----- TYPE OF CABIN -----
   * 
   * 
   */
  public function showTypes()
  {
    // get all cabin types
    $cabinTypes = CabinType::select('id', 'cabin_type')->get();

    if (!$cabinTypes) {
      return response()->json(['message' => 'No cabin types found'], 404);
    }

    return response()->json($cabinTypes);
  }


  /**
   * ----- RESERVE CABIN -----
   * 
   * 
   */
  public function reserve(Request $request)
  {
      // Check if the user already has a reserved cabin in the session
      if ($request->session()->has('reserved_cabin_id')) {
          return response()->json([
              'message' => 'You have already reserved a cabin. You can only reserve one cabin per session.'
          ], 403);
      }
  
      $cabinNumber = $request->input('cabin_number');
      $cabinTypeId = $request->input('cabin_type_id');
      $cabinCategoryId = $request->input('cabin_category_id');
  
      // Check if the cabin is available
      $filteredCabins = $this->filterCabins($cabinTypeId, $cabinCategoryId);
  
      if (isset($filteredCabins['error'])) {
          return response()->json(['message' => $filteredCabins['error']], $filteredCabins['status']);
      }
  
      // Check if the specific cabin number exists in the filtered cabins
      $cabin = $filteredCabins['cabins']->firstWhere('cabin_number', (string) $cabinNumber);
  
      if (!$cabin) {
          return response()->json(['message' => 'Cabin not found or may be reserved'], 404);
      }
  
      // Reservation logic for cabin_type_id == 1
      if ($cabinTypeId == 1) {
          $existingReservation = TemporaryReservation::where('cabin_id', $cabin['id'])
              ->where('expires_at', '>', now())
              ->first();
  
          if ($existingReservation) {
              return response()->json(['message' => 'Cabin already reserved'], 404);
          }
  
          $reserved = TemporaryReservation::updateOrCreate(
              ['cabin_id' => $cabin['id']],
              [
                  'user_id' => $request->user()->id ?? null,
                  'cabin_number' => $cabin['cabin_number'],
                  'expires_at' => Carbon::now()->addMinutes(5),
                  'inventory' => $cabin['cabin_inventory'],
              ]
          );
  
          // Store the reservation ID in the session to prevent further reservations
          $request->session()->put('reserved_cabin_id', $reserved->id);
  
          return response()->json([
              'success' => true,
              'message' => 'Cabin reserved',
              'reservation_id' => $reserved->id,
          ], 200);
      } else {
        // Logic for cabin types 2 and 3
        $existingReservations = TemporaryReservation::where('cabin_id', $cabin['id'])
            ->where('expires_at', '>', now())
            ->get();

        if ($existingReservations->count() >= $cabin['inventory']) {
            return response()->json(['message' => 'All already reserved'], 404);
        }

        $reserved = TemporaryReservation::updateOrCreate(
            ['cabin_id' => $cabin['id']],
            [
                'cabin_number' => $cabin['cabin_number'],
                'inventory' => 1,
                'expires_at' => now()->addMinutes(5)
            ]
        );

      // Store the reservation ID in the session to prevent further reservations
      $request->session()->put('reserved_cabin_id', $reserved->id);

      return response()->json([
          'success' => true,
          'message' => $reserved,
          'reservation_id' => $reserved->id,
      ], 200);
    }
  }

  /**
   * ----- RELASE THE CABIN -----
   * 
   * 
   */
  public function release(Request $request)
  {
    // Check if the user has a reserved cabin in the session
    if (!$request->session()->has('reserved_cabin_id')) {
        return response()->json(['message' => 'No cabin reserved'], 404);
    }

    $reservationId = $request->session()->get('reserved_cabin_id');
    $reservation = TemporaryReservation::find($reservationId);

    if (!$reservation) {
        return response()->json(['message' => 'No reservation found'], 404);
    }

    // Check if the reservation belongs to the authenticated user
    if ($request->user() && $reservation->user_id !== $request->user()->id) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // Delete the reservation
    $reservation->delete();

    // Remove the reserved_cabin_id from the session
    $request->session()->forget('reserved_cabin_id');

    return response()->json(['message' => 'Cabin released'], 200);
  }
}
