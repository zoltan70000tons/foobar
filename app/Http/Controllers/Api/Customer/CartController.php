<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Adjustment;
use App\Helpers\PriceCalculation;
use Illuminate\Support\Facades\Auth;
use App\Models\TemporaryReservation;

class CartController extends Controller
{
  // get items from cart session
  public function index(Request $request, $eventId)
  {
    // if cart session is empty, return an empty array else return the cart session
    $cart = $request->session()->get("cart", []);
    $eventId = $cart["event_id"] ?? $eventId;

    $adjustments = Adjustment::where("event_id", $eventId)->first();
    $singleTicketFeeAddon = $adjustments->where("code", "SINGLE_TICKET_FEE")->first()->value;
    $taxAddon = $adjustments->where("code", "TAX")->first()->value;
    $chooseYourCabinAddon = $adjustments->where("code", "CHOOSE_YOUR_CABIN")->first()->value;
    $discountPaymentFull = $adjustments->where("code", "PAID_IN_FULL")->first()->value;

    $user = Auth::check() ? Auth::user() : null;

    $customer = $user && $user->hasRole("Customer") ? $user : null;
    $membership = $customer ? $customer->membershipTypes->first() : null;

    if ($cart && isset($cart["cabin_price"])) {
      $priceCalc = PriceCalculation::calculatePricePerPassenger([
        "cabinPrice" => $cart["cabin_price"],
        "capacity" => $cart["cabin_capacity"],
        "userDiscount" => $membership->discount_value ?? null,
        "cabinType" => $cart["cabin_type"] === "private-cabin" ? true : false,
        "paymentDiscount" => $cart["payment_plan"] === "PAY_IN_FULL" ? $discountPaymentFull : 0,
        "addons" => $cart["addons"],
        "isSelection" => $cart["choose_your_cabin"],
        "singleTicketFeeAddon" => $singleTicketFeeAddon,
        "taxAddon" => $taxAddon,
        "chooseYourCabinAddon" => $chooseYourCabinAddon,
      ]);

      // add the calculated price to the cart session
      $cart["price_total"] = $priceCalc["total"];
      $cart["price_total_passenger"] = $priceCalc["totalPassenger"];
      $cart["price_save"] = $priceCalc["save"];

      // add static tax from adjustments to the cart session
      $cart["tax"] = $taxAddon;
    }

    return response()->json($cart, 200);
  }

  // Only check the user have a cart session
  public function check(Request $request)
  {
    $cart = $request->session()->get("cart", []);

    return response()->json(["cart" => $cart], 200);
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
      "reservation_id" => "nullable|integer",
      "reservation_timestamp" => "nullable|string",
      "cabin_price" => "nullable|string",
      "cabin_capacity" => "nullable|integer",
      "cabin_code" => "nullable|string",
      "cabin_category" => "nullable|integer",
      "cabin_category_decks" => "nullable|string",
      "cabin_category_type" => "nullable|string",
      "force_clear" => "nullable|boolean",
    ]);

    // if force_clear is true, delete the reservation from db and forget it from session
    if ($request->input("force_clear", false)) {
      if ($request->session()->has("reserved_cabin_id")) {
        $reservationId = $request->session()->get("reserved_cabin_id");

        $reservation = TemporaryReservation::find($reservationId);

        if ($reservation) {
          $reservation->delete();
        }

        $request->session()->forget("reserved_cabin_id");
      }
    }

    if ($request->session()->has("cart")) {
      $this->destroy($request);
    }

    session(["cart" => $validated]);

    return response()->json(["message" => "Cart updated successfully"], 200);
  }

  // Update cart session
  public function update(Request $request)
  {
    $validated = $request->validate([
      "event_id" => "required|string",
      "cabin_type" => "nullable|string",
      "addons" => "nullable|array",
      "step" => "required|integer",
      "payment_plan" => "nullable|string",
      "choose_your_cabin" => "nullable|boolean",
      "cabin_number" => "nullable|integer",
      "reservation_id" => "nullable|integer",
      "reservation_timestamp" => "nullable|string",
      "cabin_price" => "nullable|string",
      "cabin_capacity" => "nullable|integer",
      "cabin_code" => "nullable|string",
      "cabin_category" => "nullable|integer",
      "cabin_category_decks" => "nullable|string",
      "cabin_category_type" => "nullable|string",
    ]);

    $request->session()->put("cart", $validated);

    return response()->json(["message" => "Cart updated successfully"], 200);
  }

  // destroy cart session
  public function destroy(Request $request)
  {
    $request->session()->forget("cart");

    //$request->session()->forget("reserved_cabin_id");

    // if session have reserved_cabin_id, delete it from db and forget it from session
    if ($request->session()->has("reserved_cabin_id")) {
      $reservationId = $request->session()->get("reserved_cabin_id");

      $reservation = TemporaryReservation::find($reservationId);

      if ($reservation) {
        $reservation->delete();
      }

      $request->session()->forget("reserved_cabin_id");
    }

    return response()->json(["message" => "Cart cleared successfully"], 200);
  }
}
