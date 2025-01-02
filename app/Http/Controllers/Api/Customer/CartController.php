<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Adjustment;
use App\Helpers\PriceCalculation;
use Illuminate\Support\Facades\Auth;
use App\Models\TemporaryReservation;
use App\Models\CabinCategory;

class CartController extends Controller
{
  // get items from cart session
  public function index(Request $request, $eventId)
  {
    // if cart session is empty, return an empty array else return the cart session
    $cart = $request->session()->get('cart', []);
    $eventId = $cart['event_id'] ?? $eventId;

    $adjustments = Adjustment::where('event_id', $eventId)->first();
    $taxAddon = $adjustments ? $adjustments->where('code', 'TAX')->first()->value : 0;

    $user = Auth::check() ? Auth::user() : null;

    // $customer = $user && $user->hasRole('Customer') ? $user : null;
    //$membership = $customer ? $customer->membershipTypes->first() : null;

    // 1. price cart get from database based on id
    // 2. same with cabin capacity

    if ($cart && isset($cart['cabin_price'])) {
      $cabin = CabinCategory::where('event_id', $eventId)
        ->where('id', $cart['cabin_category'])
        ->first();

      if (!$cabin) {
        return response()->json(['message' => 'Cabin category not found'], 404);
      }

      $price = $cabin->price;
      $capacity = $cabin->spec->capacity;

      $priceCalc = PriceCalculation::calculatePricePerPassenger([
        'cabinPrice' => $price,
        'cabinCapacity' => $capacity,
        'cabinType' => $cart['cabin_type'] === 'private-cabin' ? true : false,
        //'userDiscount' => $membership->discount_value ?? null,
        'selectedAdjustments' => $cart['addons'],
        'adjustments' => $adjustments,
      ]);

      // add the calculated price to the cart session
      $cart['price_total'] = $priceCalc['total'];
      $cart['price_total_passenger'] = $priceCalc['totalPassenger'];
      $cart['price_save'] = $priceCalc['save'];
      $cart['price_extras'] = $priceCalc['extras'];

      // add static tax from adjustments to the cart session
      $cart['tax'] = $taxAddon;
    }

    return response()->json($cart, 200);
  }

  // Only check the user have a cart session
  public function check(Request $request)
  {
    $cart = $request->session()->get('cart', []);

    return response()->json(['cart' => $cart], 200);
  }

  // Add item to cart session
  public function store(Request $request)
  {
    $validated = $request->validate([
      'event_id' => 'required|string',
      'cabin_type' => 'nullable|string',
      'addons' => 'nullable|array',
      'step' => 'required|integer',
      'payment_plan' => 'nullable|string',
      'number_of_installments' => 'nullable|string',
      'choose_your_cabin' => 'nullable|boolean',
      'cabin_number' => 'nullable|integer',
      'reservation_id' => 'nullable|integer',
      'reservation_timestamp' => 'nullable|string',
      'cabin_price' => 'nullable|string',
      'cabin_capacity' => 'nullable|integer',
      'cabin_code' => 'nullable|string',
      'cabin_category' => 'nullable|integer',
      'cabin_category_decks' => 'nullable|string',
      'cabin_category_type' => 'nullable|string',
      'force_clear' => 'nullable|boolean',
    ]);

    // set cabin number null
    $validated['cabin_number'] = null;

    // if force_clear is true, delete the reservation from db and forget it from session
    if ($request->input('force_clear', false)) {
      if ($request->session()->has('reserved_cabin_id')) {
        $reservationId = $request->session()->get('reserved_cabin_id');

        $reservation = TemporaryReservation::find($reservationId);

        if ($reservation) {
          $reservation->delete();
        }

        $request->session()->forget('reserved_cabin_id');
      }
    }

    if ($request->session()->has('cart')) {
      $this->destroy($request);
    }

    session(['cart' => $validated]);

    return response()->json(['message' => 'Cart updated successfully'], 200);
  }

  // Update cart session
  public function update(Request $request)
  {
    $validated = $request->validate([
      'event_id' => 'required|string',
      'cabin_type' => 'nullable|string',
      'addons' => 'nullable|array',
      'step' => 'required|integer',
      'payment_plan' => 'nullable|string',
      'number_of_installments' => 'nullable|string',
      'choose_your_cabin' => 'nullable|boolean',
      'cabin_number' => 'nullable|integer',
      'reservation_id' => 'nullable|integer',
      'reservation_timestamp' => 'nullable|string',
      'cabin_price' => 'nullable|string',
      'cabin_capacity' => 'nullable|integer',
      'cabin_code' => 'nullable|string',
      'cabin_category' => 'nullable|integer',
      'cabin_category_decks' => 'nullable|string',
      'cabin_category_type' => 'nullable|string',
    ]);

    $request->session()->put('cart', $validated);

    return response()->json(['message' => 'Cart updated successfully'], 200);
  }

  // destroy cart session
  public function destroy(Request $request)
  {
    $request->session()->forget('cart');

    //$request->session()->forget("reserved_cabin_id");

    // if session have reserved_cabin_id, delete it from db and forget it from session
    if ($request->session()->has('reserved_cabin_id')) {
      $reservationId = $request->session()->get('reserved_cabin_id');

      $reservation = TemporaryReservation::find($reservationId);

      if ($reservation) {
        $reservation->delete();
      }

      $request->session()->forget('reserved_cabin_id');
    }

    return response()->json(['message' => 'Cart cleared successfully'], 200);
  }
}
