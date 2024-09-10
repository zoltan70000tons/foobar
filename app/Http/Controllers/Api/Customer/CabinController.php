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
  public function show()
  {
    //
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
