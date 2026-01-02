<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Adjustment;
use App\Helpers\PriceCalculation;
use Illuminate\Support\Facades\Auth;
use App\Models\CabinCategorySpec;
use App\Services\ReservationService;
use App\Models\Event;
use App\Models\Cart;
use App\Helpers\AgeRestriction;
use App\Helpers\ErrorResponse;
use Illuminate\Support\Str;
use App\Enums\SystemAdjustment;
use App\Enums\ErrorCode;
use App\Repositories\AdjustmentsRepository;

class CartController extends Controller {
    protected AdjustmentsRepository $adjustmentsRepository;

    protected ReservationService $reservationService;

    public function __construct(ReservationService $reservationService, AdjustmentsRepository $adjustmentsRepository) {
        $this->reservationService = $reservationService;
        $this->adjustmentsRepository = $adjustmentsRepository;
    }
    /*
  |--------------------------------------------------------------------------
  | GET CART DATA
  |--------------------------------------------------------------------------
  |
  |  Private function for re-use the cart data
  |
  */
    private function getCartData($eventId) {
        // Fetch cart from session
        $user = Auth::user();

        $cartModel = Cart::where('user_id', $user->id)->first();

        $cart = $cartModel->cart_data ?? null;
        if (!$cart || !is_array($cart) || empty($cart)) {
            return [];
        }

        $eventId = $cart['event_id'] ?? $eventId;

        // Extract adjustment codes from cart addons
        $adjustmentCodes = [];
        if (isset($cart['addons']) && is_array($cart['addons'])) {
            $adjustmentCodes = collect($cart['addons'])
                ->pluck('code')
                ->filter()
                ->toArray();
        }

        // Always include TAX (avoid duplicates)
        if (!in_array(SystemAdjustment::TAX->value, $adjustmentCodes)) {
            $adjustmentCodes[] = SystemAdjustment::TAX->value;
        }

        // Remove duplicates just in case
        $adjustmentCodes = array_unique($adjustmentCodes);

        // Fetch only the adjustments we need in a single query
        $adjustments = $this->adjustmentsRepository->getAdjustmentsByCodes($adjustmentCodes, $eventId);

        $category = CabinCategorySpec::find($cart['cabin_category']);
        $context = [
            'cabin' => [
                'category_name' => $category?->category_name,
                'code' => $cart['cabin_code'] ?? null,
                'capacity' => $cart['cabin_capacity'] ?? null,
            ],
            'event' => [
                'id' => $eventId,
                'status' => Event::find($eventId)->status,
            ],
        ];

        // Apply restrictions to adjustments
        $adjustments->transform(function ($adjustment) use ($context) {
            if (method_exists($adjustment, 'shouldApply') && !$adjustment->shouldApply($context)) {
                $adjustment->value = 0;
            }
            return $adjustment;
        });

        $taxAddon = $adjustments->where('code', SystemAdjustment::TAX->value)->first()?->value ?? 0;

        $cabinTitle =
            CabinCategorySpec::where('category_code', $cart['cabin_code'])
                ->where('capacity', $cart['cabin_capacity'])
                ->first()
                ?->cabinCategories()
                ?->first()
                ?->getTitleAttribute() ?? null;

        $errorCode = null;

        if (isset($cart['cabin_price'])) {
            $priceCalc = PriceCalculation::calculatePricePerPassenger(
                (float) $cart['cabin_price'],
                (int) $cart['cabin_capacity'],
                $cart['cabin_type'] === 'private-cabin',
                $adjustments,
            );

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

    /**
     * Validates that a cart has a valid reservation when required (step 4+).
     *
     * Checks if the cart has a valid reservation based on the current or intended step.
     * Steps 1-3 don't require a reservation. Steps 4+ require a valid, existing reservation.
     * If validation fails after step 3, the cart is cleared and an error response is returned.
     *
     * @param array $cart The cart data to validate
     * @param int|null $newCartStep Optional step number to validate when cart is being updated
     * @return \Illuminate\Http\JsonResponse|null Returns error response if invalid, null if valid
     */
    private function validateCartReservationOrFail(array $cart, $newCartStep = null) {
        // Determine which step to validate: use the provided step when cart is being updated or fall back to the cart's current when fetching
        $stepToCheck = $newCartStep ?? ($cart['step'] ?? 0);

        // Steps 1-3: Reservation is not required yet (user is still selecting cabin/options)
        if ($stepToCheck <= 3 && (!isset($cart['reservation_id']) || is_null($cart['reservation_id']))) {
            return null; // Validation passes - no reservation needed
        }

        // Steps 4+: Reservation must exist and be valid
        // Check if reservation_id is missing, null, or doesn't exist in the cart/database for this user
        if (
            !isset($cart['reservation_id']) ||
            is_null($cart['reservation_id']) ||
            !$this->reservationService->reservationExists($cart['reservation_id'], Auth::id())
        ) {
            return ErrorResponse::reservationExpired();
        }

        // Validation passes - cart has a valid reservation
        return null;
    }

    /*
  |--------------------------------------------------------------------------
  | Index
  |--------------------------------------------------------------------------
  |
  |  Fetch the cart data from db
  |
  */
    public function index($eventId) {
        $cart = $this->getCartData($eventId);

        // Validate reservation if cart is past step 3
        if ($cartValidationError = $this->validateCartReservationOrFail($cart)) {
            return $cartValidationError;
        }

        return response()->json($cart, 200);
    }

    /*
  |--------------------------------------------------------------------------
  | Store
  |--------------------------------------------------------------------------
  |
  | Store the cart data in db
  |
  */
    public function store(Request $request, ReservationService $reservationService) {
        $validated = $request->validate([
            'event_id' => 'required|numeric',
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
            'show_upgrade_offer' => 'nullable|boolean',
            'original_cabin_code' => 'nullable|string',
        ]);

        $user = Auth::user();

        if (!$user) {
            return response()->json(
                [
                    'errorLogId' => Str::uuid(),
                    'errorMessage' => 'User not authenticated',
                    'errorCode' => ErrorCode::UNAUTHORIZED->value,
                ],
                401,
            );
        }

        if ($validated['force_clear'] === true) {
            // Attempt to release the cabin
            $reservationService->releaseCabin($user);
            // Clear the cart
            Cart::where('user_id', $user->id)->delete();
        }

        // if user is male ( M ) he cant book cabin type single-female ( single-female )
        if ($validated['cabin_type'] === 'single-female' && $user->detail->gender === 'M') {
            return response()->json(
                [
                    'errorLogId' => Str::uuid(),
                    'errorMessage' => __('feedback.cabin_type_not_allowed'),
                    'errorCode' => ErrorCode::CABIN_TYPE_NOT_ALLOWED->value,
                ],
                400,
            );
        }

        // if user is female ( F ) she cant book cabin type single male ( single-male )
        if ($validated['cabin_type'] === 'single-male' && $user->detail->gender === 'F') {
            return response()->json(
                [
                    'errorLogId' => Str::uuid(),
                    'errorMessage' => __('feedback.cabin_type_not_allowed'),
                    'errorCode' => ErrorCode::CABIN_TYPE_NOT_ALLOWED->value,
                ],
                400,
            );
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
            'upgrade_cabin' => null,
        ];

        $mergedCart = array_merge($defaultCart, $validated);

        $user = Auth::user();

        // Age verification
        $dateOfBirth = $user->detail->dob ?? null;
        if ($dateOfBirth) {
            // If age restricion is false return error
            $isAgeValid = AgeRestriction::isAgeValid($dateOfBirth, 21);
            if (!$isAgeValid) {
                return response()->json(
                    [
                        'errorLogId' => Str::uuid(),
                        'errorMessage' => __('feedback.age_restriction'),
                        'errorCode' => ErrorCode::AGE_RESTRICTION->value,
                    ],
                    422,
                );
            }
        } else {
            return response()->json(
                [
                    'errorLogId' => Str::uuid(),
                    'errorMessage' => 'Date of birth is required',
                    'errorCode' => ErrorCode::DOB_REQUIRED->value,
                ],
                422,
            );
        }

        Cart::updateOrCreate(['user_id' => $user->id], ['cart_data' => $mergedCart]);

        return response()->json(
            [
                'message' => 'Cart updated successfully',
                'cart' => $this->getCartData($validated['event_id']),
            ],
            200,
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
    public function update(Request $request, ReservationService $reservationService) {
        $validated = $request->validate([
            'event_id' => 'required|numeric',
            'cabin_type' => 'nullable|string',
            'addons' => 'nullable|array',
            'step' => 'required|integer',
            'payment_plan' => 'nullable|string',
            'number_of_installments' => 'nullable|integer',
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
            'show_upgrade_offer' => 'nullable|boolean|sometimes',
            'original_cabin_code' => 'nullable|string|sometimes',
        ]);

        $user = Auth::user();

        if (!$user) {
            return response()->json(
                [
                    'errorLogId' => Str::uuid(),
                    'errorMessage' => 'User not authenticated',
                    'errorCode' => ErrorCode::UNAUTHORIZED->value,
                ],
                401,
            );
        }

        /// current state of cart
        $existingCart = Cart::where('user_id', $user->id)->first()?->cart_data ?? [];

        \Log::info('Existing Cart:', $existingCart);

        // if cart is empty return error
        if (empty($existingCart)) {
            return ErrorResponse::reservationExpired();
        }

        if ($cartValidationError = $this->validateCartReservationOrFail($existingCart, $validated['step'])) {
            return $cartValidationError;
        }

        // Age verification
        $dateOfBirth = $user->detail->dob ?? null;
        if ($dateOfBirth) {
            // If age restricion is false return error
            $isAgeValid = AgeRestriction::isAgeValid($dateOfBirth, 21);
            if (!$isAgeValid) {
                return response()->json(
                    [
                        'errorLogId' => Str::uuid(),
                        'errorMessage' => __('feedback.age_restriction'),
                        'errorCode' => ErrorCode::AGE_RESTRICTION->value,
                    ],
                    422,
                );
            }
        } else {
            return response()->json(
                [
                    'errorLogId' => Str::uuid(),
                    'errorMessage' => 'Date of birth is required',
                    'errorCode' => ErrorCode::DOB_REQUIRED->value,
                ],
                422,
            );
        }

        // handle choose dynamic selection of choose your cabin
        // if the user changed from we_pick to you_pick we need to release the cabin
        if (isset($existingCart['choose_your_cabin']) && isset($validated['choose_your_cabin'])) {
            if ($existingCart['choose_your_cabin'] === false && $validated['choose_your_cabin'] === true) {
                // release cabin
                $reservationService->releaseCabin($user);
                // clear cabin_number and reservation_id from cart
                $validated['cabin_number'] = null;
                $validated['reservation_id'] = null;
                $validated['reservation_timestamp'] = null;
            }
        }

        Cart::updateOrCreate(['user_id' => $user->id], ['cart_data' => $validated]);

        return response()->json(
            [
                'message' => 'Cart updated successfully',
                'cart' => $this->getCartData($validated['event_id']),
            ],
            200,
        );
    }

    /*
  |--------------------------------------------------------------------------
  | Destroy
  |--------------------------------------------------------------------------
  |
  |  Clear the cart data from db
  |
  */
    public function destroy(ReservationService $reservationService) {
        $user = Auth::user();

        if (!$user) {
            return response()->json(
                [
                    'errorLogId' => Str::uuid(),
                    'errorMessage' => 'User not authenticated',
                    'errorCode' => ErrorCode::UNAUTHORIZED->value,
                ],
                401,
            );
        }

        Cart::where('user_id', $user->id)->delete();

        $reservationService->releaseCabin($user);

        return response()->json(['message' => 'Cart cleared successfully'], 200);
    }
}
