<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cabin;
use App\Events\CabinChange;
use Illuminate\Support\Facades\Log;

class CabinController extends Controller
{
  public function show()
  {
    // return cabins where cabin_category_id === 1
    $cabins = Cabin::where('cabin_category_id', 1)->get();
    return response()->json($cabins);
  }


  public function trigger()
  {
    // return cabins where cabin_category_id === 1
    $cabins = Cabin::where('cabin_category_id', 1)->get();

    // reaoder cabins on each triiger
    $cabins = $cabins->shuffle();

    // get first cabin
    CabinChange::dispatch($cabins);
  }
}
