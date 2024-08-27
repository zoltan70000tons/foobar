<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class BookingInitController extends Controller
{

  // test
  public function store(Request $request): JsonResponse
  {
    return response()->json(['message' => 'test message']);
  }
}
