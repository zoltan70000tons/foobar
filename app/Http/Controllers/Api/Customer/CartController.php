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
  /*
  |--------------------------------------------------------------------------
  | Index
  |--------------------------------------------------------------------------
  |
  |  Fetch the cart data from session
  |
  */
  public function index(Request $request, $eventId)
  {
    // Reload session to ensure latest data
    $cart = $request->session()->get('cart', []);
    $eventId = $cart['event_id'] ?? $eventId;

    if (!$cart) {
      \Log::info('Cart is empty');
      return response()->json(['cart' => []], 200);
    }

    // Fetch adjustments and tax
    $adjustments = Adjustment::where('event_id', $eventId)->get();
    $taxAddon = $adjustments->where('code', 'TAX')->first()?->value ?? 0;

    if (isset($cart['cabin_price'])) {
      $priceCalc = PriceCalculation::calculatePricePerPassenger([
        'cabinPrice' => (float) $cart['cabin_price'],
        'cabinCapacity' => (int) $cart['cabin_capacity'],
        'cabinType' => $cart['cabin_type'] === 'private-cabin',
        'selectedAdjustments' => $cart['addons'],
        'adjustments' => $adjustments,
      ]);

      $cart = array_merge($cart, [
        'price_total' => $priceCalc['total'],
        'price_total_passenger' => $priceCalc['totalPassenger'],
        'price_save' => $priceCalc['save'],
        'price_extras' => $priceCalc['extras'],
        'tax' => $taxAddon,
      ]);
    }

    \Log::info('CartController@index', ['cart' => $cart]);
    return response()->json($cart, 200);
  }

  /*
  |--------------------------------------------------------------------------
  | Check JG I WIll remove this but i want to keep it for now
  |--------------------------------------------------------------------------
  |
  | Check if the cart data is in session
  |
  */
  // public function check(Request $request)
  // {
  //   $cart = $request->session()->get('cart', []);

  //   return response()->json(['cart' => $cart], 200);
  // }

  /*
  |--------------------------------------------------------------------------
  | Store
  |--------------------------------------------------------------------------
  |
  | Store the cart data in session
  |
  */
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

    // Set cabin_number to null
    $validated['cabin_number'] = null;

    // Force clear session if requested
    if ($request->input('force_clear', false)) {
      \Log::info('Force clearing cart session');
      $request->session()->forget('cart');
    }

    // Save validated data to session
    $defaultCart = [
      'event_id' => null,
      'cabin_type' => null,
      'addons' => [],
      'step' => null,
      'reservation_id' => null,
      'reservation_timestamp' => null,
      'cabin_price' => null,
      'cabin_capacity' => null,
      'cabin_category' => null,
    ];

    $mergedCart = array_merge($defaultCart, $validated);

    \Log::info('Saving to session', ['cart' => $mergedCart]);
    session(['cart' => $mergedCart]);

    return response()->json(['message' => 'Cart updated successfully'], 200);
  }

  /*
  |--------------------------------------------------------------------------
  | Update
  |--------------------------------------------------------------------------
  |
  |  Update the cart data in session
  |
  */
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
      'cabin_category_type' => 'required|string',
    ]);

    $request->session()->put('cart', $validated);

    return response()->json(['message' => 'Cart updated successfully'], 200);
  }

  /*
  |--------------------------------------------------------------------------
  | Destroy
  |--------------------------------------------------------------------------
  |
  |  Clear the cart data from session
  |
  */
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
