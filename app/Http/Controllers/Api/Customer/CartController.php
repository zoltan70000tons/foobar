<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CartController extends Controller
{
  // get items from cart session
  public function index(Request $request)
  {
    // if cart session is empty, return an empty array else return the cart session

    $cart = $request->session()->get("cart", []);

    \Log::info($cart);

    return response()->json($cart, 200);
  }

  // Add item to cart session
  public function store(Request $request)
  {
    $validated = $request->validate([
      "event_id" => "required|string",
      "cabin_type" => "nullable|string",
      "addons" => "nullable|array",
      "step" => "required|integer",
      "payment_plan" => "nullable|string",
      "choose_your_cabin" => "nullable|boolean",
      "cabin_number" => "nullable|integer",
      "reservation_id" => "nullable|string",
      "reservation_timestamp" => "nullable|string",
      "cabin_price" => "required|string",
      "cabin_capacity" => "required|integer",
      "cabin_code" => "nullable|string",
      "cabin_category" => "required|integer",
      "cabin_category_decks" => "required|string",
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
