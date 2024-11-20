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
    $request->validate([
      "id" => "required",
      "quantity" => "required|integer|min:1",
    ]);

    $cart = $request->session()->get("cart", []);

    $cart[$request->id] = $request->quantity;

    $request->session()->put("cart", $cart);

    return response()->json(["message" => "Item added to cart"], 200);
  }
}
