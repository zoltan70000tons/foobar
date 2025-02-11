<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Adjustment;
use App\Helpers\PriceCalculation;
use Illuminate\Support\Facades\Auth;
use App\Models\TemporaryReservation;
use App\Models\CabinCategory;
use App\Services\ReservationService;
use App\Models\Event;

class CartController extends Controller
{
  /*
  |--------------------------------------------------------------------------
  | GET CART DATA
  |--------------------------------------------------------------------------
  |
  |  Private function for re-use the cart data
  |
  */
  private function getCartData(Request $request, $eventId)
  {
    // Fetch cart from session
    $cart = $request->session()->get('cart', []);
    $eventId = $cart['event_id'] ?? $eventId;

    if (empty($cart)) {
      return [];
    }

    // Fetch adjustments and tax
    $adjustments = Adjustment::where('event_id', $eventId)->get();
    $taxAddon = $adjustments->where('code', 'TAX')->first()?->value ?? 0;
    $eventStatus = Event::find($eventId)->status;

    $errorCode = null;
    if (Auth::check() && $eventId && Auth::user()->bookings()->where('event_id', $eventId)->count() > 0) {
      $errorCode .= 'BOOKING_LIMIT_EXCEEDED';
    }

    if (isset($cart['cabin_price'])) {
      $priceCalc = PriceCalculation::calculatePricePerPassenger([
        'cabinPrice' => (float) $cart['cabin_price'],
        'cabinCapacity' => (int) $cart['cabin_capacity'],
        'cabinType' => $cart['cabin_type'] === 'private-cabin',
        'selectedAdjustments' => $cart['addons'],
        'adjustments' => $adjustments,
        'eventStatus' => $eventStatus,
      ]);

      $cart = array_merge($cart, [
        'price_total' => $priceCalc['total'],
        'price_total_passenger' => $priceCalc['totalPassenger'],
        'price_save' => $priceCalc['save'],
        'price_extras' => $priceCalc['extras'],
        'tax' => $taxAddon,
        'error_code' => $errorCode,
      ]);
    }

    return $cart;
  }

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
    $cart = $this->getCartData($request, $eventId);

    \Log::info('CartController@index', ['cart' => $cart]) ?? null;
    return response()->json($cart, 200);
  }

  /*
  |--------------------------------------------------------------------------
  | Store
  |--------------------------------------------------------------------------
  |
  | Store the cart data in session
  |
  */
  public function store(Request $request, ReservationService $reservationService)
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
      'cabin_conf_accp' => 'nullable|boolean',
      'force_clear' => 'nullable|boolean',
      'single_t_agreement' => 'boolean',
    ]);

    if ($validated['force_clear'] === true) {
      \Log::info('Force clearing cart session');

      // Attempt to release the cabin
      $releaseResponse = $reservationService->releaseCabin($request);

      if ($releaseResponse['status'] === 404) {
        \Log::warning('No cabin reserved to release. Proceeding with clearing the cart.');
      } elseif ($releaseResponse['status'] === 200) {
        \Log::info('Cabin successfully released during force clear.');
      }

      // Always clear the cart regardless of the reservation status
      $request->session()->forget('cart');
    }

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

    $cart = $this->getCartData($request, $validated['event_id']) ?? null;

    return response()->json(
      [
        'message' => 'Cart updated successfully',
        'cart' => $cart,
      ],
      200
    );
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
      'cabin_conf_accp' => 'nullable|boolean',
      'cabin_number' => 'nullable|integer',
      'reservation_id' => 'nullable|integer',
      'reservation_timestamp' => 'nullable|string',
      'cabin_price' => 'nullable|string',
      'cabin_capacity' => 'nullable|integer',
      'cabin_code' => 'nullable|string',
      'cabin_category' => 'nullable|integer',
      'cabin_category_decks' => 'nullable|string',
      'cabin_category_type' => 'required|string',
      'single_t_agreement' => 'boolean',
      'time_to_cancel' => 'nullable|integer',
    ]);

    $request->session()->put('cart', $validated);

    $cart = $this->getCartData($request, $validated['event_id']) ?? null;

    return response()->json(
      [
        'message' => 'Cart updated successfully',
        'cart' => $cart,
      ],
      200
    );
  }

  /*
  |--------------------------------------------------------------------------
  | Destroy
  |--------------------------------------------------------------------------
  |
  |  Clear the cart data from session
  |
  */
  public function destroy(Request $request, ReservationService $reservationService)
  {
    $request->session()->forget('cart');

    // Attempt to release the cabin
    $releaseResponse = $reservationService->releaseCabin($request);

    if ($releaseResponse['status'] === 404) {
      \Log::warning('No cabin reserved to release. Proceeding with clearing the cart.');
    } elseif ($releaseResponse['status'] === 200) {
      \Log::info('Cabin successfully released during cart clear.');
    }

    return response()->json(['message' => 'Cart cleared successfully'], 200);
  }
}
