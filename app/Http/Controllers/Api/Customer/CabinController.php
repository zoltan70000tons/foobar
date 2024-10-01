<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cabin;
use App\Models\CabinType;
use App\Events\CabinChange;
use Illuminate\Support\Facades\Log;

class CabinController extends Controller
{


  public function show($cabinTypeId, $cabinCategoryId, $cabinDeck)
  {
    // get all cabins
    $cabins = Cabin::where('cabin_type_id', $cabinTypeId)
      ->where('cabin_category_id', $cabinCategoryId)
      ->where('deck', $cabinDeck)
      ->get();

    if (!$cabins) {
      return response()->json(['message' => 'No cabins found'], 404);
    }

    $formattedCabins = $cabins->map(function ($cabin) {
      return [
        'id' => $cabin->id,
        'cabin_number' => $cabin->cabin_number,
        'deck' => $cabin->deck,
        'status' => $cabin->status,
        'accessible' => $cabin->accessible,
        'balcony' => $cabin->balcony,
        'cabin_type_id' => $cabin->cabin_type_id,
        'cabin_category_id' => $cabin->cabin_category_id,
      ];
    });

    return response()->json($formattedCabins);
  }

  public function showTypes()
  {
    // get all cabin types
    $cabinTypes = CabinType::select('id', 'cabin_type')->get();

    if (!$cabinTypes) {
      return response()->json(['message' => 'No cabin types found'], 404);
    }

    return response()->json($cabinTypes);
  }

  public function trigger()
  {
    // 
  }
}
