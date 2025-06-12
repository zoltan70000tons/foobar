<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Adjustment;
use App\Helpers\PriceCalculation;
use Illuminate\Support\Facades\Auth;
use App\Models\TemporaryReservation;
use App\Models\CabinCategory;
use App\Models\CabinCategorySpec;
use App\Services\ReservationService;
use App\Models\Event;
use App\Models\Cart;
use App\Helpers\AgeRestriction;

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
    $user = Auth::user();
    // $cart = $user ? Cart::where('user_id', $user->id)->first()?->cart_data ?? [] : $request->session()->get('cart', []);

    $cart = $user ? Cart::where('user_id', $user->id)->first()?->cart_data ?? [] : [];

    if (empty($cart)) {
      return [];
    }

    $eventId = $cart['event_id'] ?? $eventId;
    // Fetch adjustments and tax
    $adjustments = Adjustment::where('event_id', $eventId)->get();
    $taxAddon = $adjustments->where('code', 'TAX')->first()?->value ?? 0;

    $cabinTitle = CabinCategorySpec::where('category_code', $cart['cabin_code'])
      ->where('capacity', $cart['cabin_capacity'])
      ->first()
      ?->cabinCategories()
      ?->first()
      ?->getTitleAttribute() ?? null;
      
    $eventStatus = Event::find($eventId)->status;
    $errorCode = null;

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
        'cabin_title' => $cabinTitle,
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
      'cabin_price' => 'required|string',
      'cabin_capacity' => 'required|integer',
      'cabin_code' => 'required|string',
      'cabin_category' => 'nullable|integer',
      'cabin_category_decks' => 'nullable|string',
      'cabin_category_type' => 'nullable|string',
      'cabin_conf_accp' => 'nullable|boolean',
      'force_clear' => 'nullable|boolean',
      'single_t_agreement' => 'boolean',
      'price_total' => 'nullable|numeric|sometimes',
      'price_total_passenger' => 'nullable|numeric|sometimes',
      'price_save' => 'nullable|string|sometimes',
      'price_extras' => 'nullable|numeric|sometimes',
      'tax' => 'nullable|numeric|sometimes',
      'lower_bed_type_2' => 'nullable|string',
    ]);

    $user = Auth::user();

    if (!$user) {
      return response()->json(['message' => 'User not authenticated'], 401);
    }

    if ($validated['force_clear'] === true) {
      // Attempt to release the cabin
      $reservationService->releaseCabin($request);

      // Always clear the cart regardless of the reservation status
      //$request->session()->forget('cart');
      // Clear the cart from the database
      // $user = Auth::user();

      // if ($user) {
      //   Cart::where('user_id', $user->id)->delete();
      // }

      Cart::where('user_id', $user->id)->delete();
    }

    // if user is male ( M ) he cant book cabin type single-female ( single-female )
    if ($validated['cabin_type'] === 'single-female' && $user->detail->gender === 'M') {
      return response()->json(['message' => __('feedback.cabin_type_not_allowed')], 400);
    }

    // if user is female ( F ) she cant book cabin type single male ( single-male ) 
    if ($validated['cabin_type'] === 'single-male' && $user->detail->gender === 'F') {
      return response()->json(['message' => __('feedback.cabin_type_not_allowed')], 400);
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

    $user = Auth::user();

    // Age verification
    $dateOfBirth = $user->detail->dob ?? null;
    if ($dateOfBirth) {
      // If age restricion is false return error
      $isAgeValid = AgeRestriction::isAgeValid($dateOfBirth, 21);
      if (!$isAgeValid) {
        return response()->json(['message' => __('feedback.age_restriction')], 400);
      }
    } else {
      return response()->json(['message' => 'Date of birth is required'], 400);
    }

    // if ($user) {
    //   Cart::updateOrCreate(['user_id' => $user->id], ['cart_data' => $mergedCart]);
    // } else {
    //   session(['cart' => $mergedCart]);
    // }

    Cart::updateOrCreate(['user_id' => $user->id], ['cart_data' => $mergedCart]);

    return response()->json(
      [
        'message' => 'Cart updated successfully',
        'cart' => $this->getCartData($request, $validated['event_id']),
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
      'cabin_code' => 'required|string',
      'cabin_category' => 'nullable|integer',
      'cabin_category_decks' => 'nullable|string',
      'cabin_category_type' => 'required|string',
      'single_t_agreement' => 'boolean',
      'time_to_cancel' => 'nullable|integer',
      'price_total' => 'nullable|numeric|sometimes',
      'price_total_passenger' => 'nullable|numeric|sometimes',
      'price_save' => 'nullable|string|sometimes',
      'price_extras' => 'nullable|numeric|sometimes',
      'tax' => 'nullable|numeric|sometimes',
      'lower_bed_type_2' => 'nullable|string',
    ]);

    $user = Auth::user();

    if (!$user) {
      return response()->json(['message' => 'User not authenticated'], 401);
    }

    // Age verification
    $dateOfBirth = $user->detail->dob ?? null;
    if ($dateOfBirth) {
      // If age restricion is false return error
      $isAgeValid = AgeRestriction::isAgeValid($dateOfBirth, 21);
      if (!$isAgeValid) {
        return response()->json(['message' => __('feedback.age_restriction')], 400);
      }
    } else {
      return response()->json(['message' => 'Date of birth is required'], 400);
    }

    // if ($user) {
    //   Cart::updateOrCreate(['user_id' => $user->id], ['cart_data' => $validated]);
    // } else {
    //   session(['cart' => $validated]);
    // }

    Cart::updateOrCreate(['user_id' => $user->id], ['cart_data' => $validated]);

    return response()->json(
      [
        'message' => 'Cart updated successfully',
        'cart' => $this->getCartData($request, $validated['event_id']),
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
    $user = Auth::user();

    if (!$user) {
      return response()->json(['message' => 'User not authenticated'], 401);
    }

    // if ($user) {
    //   Cart::where('user_id', $user->id)->delete();
    // } else {
    //   $request->session()->forget('cart');
    // }

    Cart::where('user_id', $user->id)->delete();

    $reservationService->releaseCabin($request);

    return response()->json(['message' => 'Cart cleared successfully'], 200);
  }
}
