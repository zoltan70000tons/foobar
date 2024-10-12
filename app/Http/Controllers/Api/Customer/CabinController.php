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


class CabinController extends Controller
{


  
  /**
   * ----- SHOW CABIN -----
   * 
   * 
   */
  public function show($cabinTypeId, $cabinCategoryId, $cabinDeck)
  {

    $cabins = Cabin::with('category')
      ->where('cabin_type_id', $cabinTypeId)
      ->where('cabin_category_id', $cabinCategoryId)
      ->where('deck', $cabinDeck)
      // Exclude cabins with active temporary reservations
      ->whereDoesntHave('temporaryReservations', function ($query) {
          $query->where('expires_at', '>', Carbon::now());
      })
      ->get();

    // return response()->json($formattedCabins);
    // $cabins = Cabin::with('category')
    //   ->where('cabin_type_id', $cabinTypeId)
    //   ->where('cabin_category_id', $cabinCategoryId)
    //   ->where('deck', $cabinDeck)
    //   ->get();

    // Check if any cabins are found
    if ($cabins->isEmpty()) {
      return response()->json(['message' => 'No cabins found'], 404);
    }

    // Further filter cabins based on status and inventory
    $formattedCabins = $cabins->filter(function ($cabin) {
        if ($cabin->cabin_type_id == 1) {
            // For cabin_type_id == 1, ensure no active reservations
            return $cabin->status === StatusCabin::AVAILABLE->value;
        } else {
            // For other types, check if active reservations are less than inventory
            $activeReservations = $cabin->temporaryReservations()->where('expires_at', '>', Carbon::now())->count();
            return $cabin->status === StatusCabin::AVAILABLE->value && $activeReservations < $cabin->inventory;
        }
    })->map(function ($cabin) {
        return [
            'id' => $cabin->id,
            'cabin_number' => (string) $cabin->cabin_number,
            'deck' => $cabin->deck,
            'status' => $cabin->status,
            'accessible' => $cabin->accessible,
            'balcony' => $cabin->balcony,
            'cabin_type_id' => $cabin->cabin_type_id,
            'cabin_category_id' => $cabin->cabin_category_id,
            'cabin_category_name' => $cabin->category->category_name,
        ];
    });

    return response()->json($formattedCabins);
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
    $cabinNumber = $request->input('cabin_number');
    $cabinTypeId = $request->input('cabin_type_id');
    $cabinCategoryId = $request->input('cabin_category_id');

    // check if cabin exists
    $cabin = Cabin::where('cabin_type_id', $cabinTypeId)
      ->where('cabin_category_id', $cabinCategoryId)
      ->where('cabin_number', $cabinNumber)
      ->where('status', StatusCabin::AVAILABLE->value)
      ->first();

    if (!$cabin) {
      return response()->json(['message' => 'Cabin not found or may be reserved'], 404);
    }

    // check if cabin type is 1
    if( $cabinTypeId == 1 ) {
      $existingReservation = TemporaryReservation::where('cabin_id', $cabin->id)
        ->where('expires_at', '>', now())
        ->first();

      if ($existingReservation) {
        return response()->json(['message' => 'Cabin already reserved'], 404);
      }

      TemporaryReservation::updateOrCreate([
        'cabin_id' => $cabin->id,
        'user_id' => $request->user()->id ?? null,
        'cabin_number' => $cabin->cabin_number,
        'expires_at' => Carbon::now()->addMinutes(5),
        'inventory' => $cabin->inventory,
      ]);

      return response()->json([
        'success' => true,
        'message' => 'Cabin reserved'
      ], 200);
    } else {
      // check if cabin type is 2 or 3
      $existingReservations = TemporaryReservation::where('cabin_id', $cabin->id)
        ->where('expires_at', '>', now())
        ->get();

      if ($existingReservations->count() >= $cabin->inventory) {
        return response()->json(['message' => 'All already reserved'], 404);
      }

      $reserved = TemporaryReservation::updateOrCreate(
        ['cabin_id' => $cabin->id],
        [
          'cabin_number' => $cabin->cabin_number,
          'inventory' => 1,
          'expires_at' => now()->addMinutes(5)
        ]
      );

      return response()->json([
        'success' => true,
        'message' => $reserved,
      ], 200);
    }
  }
}
