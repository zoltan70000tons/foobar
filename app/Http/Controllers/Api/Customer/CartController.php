<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CartController extends Controller
{
  // get items from cart session
  public function index(Request $request)
  {
    $cart = $request->session()->get("cart", []);

    return response()->json($cart, 200);
  }

  // Add item to cart session
  public function store(Request $request)
  {
    $validated = $request->validate([
      "event" => "required|string",
      "ticketType" => "nullable|string",
      "addons" => "nullable|array",
      "step" => "required|integer",
      "paymentPlan" => "nullable|string",
      "cabinSelection" => "nullable|string",
      "selectedRoom" => "nullable|string",
      "reservationId" => "nullable|string",
      "reservationTimestamp" => "nullable|string",
      "cabinPrice" => "required|string",
      "cabinCapacity" => "required|integer",
      "cabinCode" => "nullable|string",
      "cabinCategory" => "required|integer",
      "categoryDecks" => "required|string",
    ]);

    session(["cart" => $validated]);

    return response()->json(["message" => "Cart updated successfully"], 200);
  }

  // destroy cart session
  public function destroy(Request $request)
  {
    $request->session()->forget("cart");
    $request->session()->forget("reserved_cabin_id");

    return response()->json(["message" => "Cart cleared successfully"], 200);
  }
}
