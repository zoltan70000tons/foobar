<?php

namespace App\Http\Controllers\Api\Customer;

use App;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cabin;
use App\Models\CabinCategory;
use App\Models\TemporaryReservation;
use App\Traits\CabinFilter;
use App\Traits\DecksFilter;
use Illuminate\Support\Facades\DB;
use App\Services\ReservationService;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Arr;
use App\Enums\CabinType;
use App\Enums\StatusCabin;
use App\Helpers\ErrorResponse;
use Log;

class CabinController extends Controller {
    use CabinFilter, DecksFilter;
    /**
     * Show all cabins for a specific cabin type based on filters.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request) {
        $language = $request->input('language', 'en');
        App::setLocale($language);

        // Convert parameters to the correct type
        $cabinTypeId = intval($request->input('cabin_type_id', 1));

        if ($cabinTypeId !== 1) {
            return response()->json(['message' => 'Option available only for private cabins.'], 404);
        }

        $cabinCategoryCode = $request->input('category_code', null);
        $cabinCapacity = $request->input('capacity', null);
        $cabinDeck = $request->input('deck', null);

        // FIRST DECK FROM CART !IMPORTANT
        // IF DECK IS NOT SELECTED FOR EXAMPLE. FIRST INIT OF PAGE
        // OR

        // if all requests parameters are null, try get from cart
        if (is_null($cabinCategoryCode) && is_null($cabinCapacity)) {
            $cart = Auth::user() ? Cart::where('user_id', Auth::id())->first()?->cart_data ?? [] : [];
            $cart = (array) $cart;

            Log::info('Cart data:', ['cart' => $cart]);

            $cabinCategoryCode = $cart['cabin_code'] ?? null;
            $cabinCapacity = $cart['cabin_capacity'] ?? null;
        }

        // if still all requests parameters are null, return error
        if (is_null($cabinCategoryCode) && is_null($cabinCapacity) && is_null($cabinDeck)) {
            return response()->json(['message' => 'Cabin category code, capacity.'], 400);
        }

        // decks with cabins only
        $decks = $this->filterDecks($cabinTypeId, $cabinCategoryCode, true, $cabinCapacity);

        // first deck with lowest number
        $cabinDeck = $cabinDeck ?? collect($decks)->first();

        // if cabinDeck is not in decks, return error
        if (!$cabinDeck) {
            return response()->json(['message' => 'No decks found for the given parameters.'], 404);
        }

        // Filter cabins based on the provided parameters
        $filteredCabins = $this->filterCabins(
            $cabinTypeId,
            null,
            $cabinDeck,
            false,
            $cabinCategoryCode,
            $cabinCapacity,
        );

        if (isset($filteredCabins['error'])) {
            return response()->json(['message' => $filteredCabins['error']], $filteredCabins['status']);
        }

        return response()->json(
            [
                'decks' => $decks,
                'cabins' => $filteredCabins['cabins'],
            ],
            200,
        );
    }

    /**
     * Show a single cabin category by ID.
     */
    public function showCategory($categoryId) {
        // Eager-load spec for static attributes
        $category = CabinCategory::with('spec')->find($categoryId);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        return response()->json($category);
    }

    /*
  |--------------------------------------------------------------------------
  | Show cabin types
  |--------------------------------------------------------------------------
  |
  |  We have in db only 3 types of cabins.
  |  So we dont need to use eloquent model for this. and we can cache it.
  |
  */
    public function showTypes() {
        // $cabinTypes = CabinType::select('id', 'cabin_type')->get();
        $cabinTypes = Cache::remember('cabin_types', now()->addHours(12), function () {
            return DB::table('cabin_types')->select('id', 'cabin_type')->get();
        });

        if ($cabinTypes->isEmpty()) {
            return response()->json(['message' => 'No cabin types found'], 404);
        }

        return response()->json($cabinTypes);
    }

    /**
     * Returns a list of cabin categories that are available for upgrade,
     * along with the price difference. Only returns categories that have at least one
     * available cabin matching the specified criteria.
     *
     * @param Request $request HTTP request containing:
     *   - event_id (int, required): The event ID to search upgrades for
     *   - cabin_capacity (int, required): Current cabin capacity (e.g., 2, 3, 4)
     *   - cabin_category_type (string, required): Current category type
     *     Options: "Interior", "Ocean View", "Balcony", "Suite"
     *   - cabin_price (float, required): Current cabin price (used to calculate price difference)
     *   - cabin_type (string, required): Type of cabin
     *     Options: "private-cabin", "single-male", "single-female"
     *
     * @return
     *   - All cabin category fields (id, event_id, price, etc.)
     *   - price_difference (float): Additional cost to upgrade
     *   - cabin_type_id (int): The cabin type ID
     *   - original_cabin_code (string): The category code being upgraded from
     */
    public function showUpgrades(Request $request) {
        $eventId = $request->get('event_id');
        $cabinCapacity = $request->get('cabin_capacity');
        $cabinCategoryType = $request->get('cabin_category_type');
        $cabinCategoryCode = $request->get('cabin_code');
        $cabinPrice = $request->get('cabin_price');
        $cabinTypeId =
            $request->get('cabin_type') === 'private-cabin'
                ? CabinType::PRIVATE_CABIN
                : ($request->get('cabin_type') === 'single-male'
                    ? CabinType::SINGLE_MALE
                    : CabinType::SINGLE_FEMALE);

        // Upgrade mapping based on cabin category type
        $upgradeMap = [
            'Interior' => ['Ocean View', 'Balcony'],
            'Ocean View' => ['Balcony', 'Suite'],
            'Balcony' => ['Suite', 'Suite'],
            'Suite' => ['Suite'],
        ];

        $upgrades = $upgradeMap[$cabinCategoryType] ?? [];

        if (empty($upgrades)) {
            return response()->json(['error' => 'No upgrades available for this cabin type'], 400);
        }

        // Single optimized query to get all potential upgrade cabins
        $upgradeResults = collect();
        $currentMinPrice = $cabinPrice;

        foreach ($upgrades as $upgradeType) {
            // Get Upgrade option following the criteria below:
            // - Category Capacity must be the same as current cabin
            // - Price must be greater than current category price
            // - Contains at least one cabin that:
            // -- Matches the same Cabin type (Private Cabin / Singles Male / Single Female)
            // -- Status is AVAILABLE or PARTIALLY_BOOKED
            // -- Exclude cabins with active temporary reservations
            $categoryUpgradeOption = CabinCategory::where('event_id', $eventId)
                ->whereHas('spec', function ($q) use ($cabinCapacity, $upgradeType) {
                    $q->where('capacity', $cabinCapacity)->where('category_type', $upgradeType);
                })
                ->where('price', '>', $currentMinPrice)
                ->whereHas('cabins', function ($q) use ($cabinTypeId) {
                    $q->where('cabin_type_id', $cabinTypeId)
                        ->whereIn('status', [StatusCabin::AVAILABLE, StatusCabin::PARTIALLY_BOOKED])
                        ->whereDoesntHave('temporaryReservations', function ($subQ) {
                            $subQ->where('expires_at', '>', now());
                        });
                })
                ->orderBy('price')
                ->first();

            if (!$categoryUpgradeOption) {
                continue;
            }

            // Update currentMinPrice for next iteration to avoid selecting the same category
            $currentMinPrice = $categoryUpgradeOption->price;

            // Remove the spec relationship to avoid duplication
            $categoryUpgradeOption->makeHidden('spec');

            // Add additional fields for frontend use
            $categoryUpgradeOption->price_difference = $categoryUpgradeOption->price - $cabinPrice;
            $categoryUpgradeOption->cabin_type_id = $cabinTypeId;
            $categoryUpgradeOption->original_cabin_code = $cabinCategoryCode;

            // Return qualifying upgrade cabin category + price with current adjustments
            $upgradeResults->push($categoryUpgradeOption);
        }

        return response()->json($upgradeResults);
    }

    /*
  |--------------------------------------------------------------------------
  | Reserve a specific cabin for the user.
  |--------------------------------------------------------------------------
  |
  |  ONLY FOR PRIVATE CABINS !!!
  |
  |  User select a specific cabin.
  |
  |  If user have a reservation, and he want to create new.
  |  Release the current cabin but keep reservation_timestamp in session.
  |
  */
    public function reserveCabinInType(Request $request, ReservationService $reservationService) {
        $user = Auth::user();
        $language = $request->input('language', 'en');
        App::setLocale($language);

        // REQUEST INPUT DATA
        $cabinNumber = $request->input('cabin_number');
        $cabinTypeId = $request->input('cabin_type_id');
        $cabinCapacity = $request->input('cabin_capacity');
        $cabinCategoryCode = $request->input('category_code');

        $cart = $user ? Cart::where('user_id', $user->id)->first()?->cart_data ?? [] : [];

        // if cart is empty, return error
        if (empty($cart)) {
            return ErrorResponse::cartEmpty();
        }

        $reservationId = $cart['reservation_id'] ?? null;
        $keepOldTimeStamp = null;

        // if cabin type id is not 1, return error
        if ($cabinTypeId !== 1) {
            return response()->json(['message' => 'Option available only for private cabin'], 400);
        }

        // ------- If user have a reservation, and he want to create new.
        if ($cart && $reservationId) {
            // get from Temporary reservation table, and set expires_at to keepOldTimeStamp
            $keepOldTimeStamp = TemporaryReservation::where('id', $reservationId)->value('expires_at');

            // release the current reservation
            $reservationService->releaseCabin($user);

            $cart['cabin_number'] = null;
            $cart['reservation_id'] = null;
            $cart['reservationTimestamp'] = null;
            Cart::updateOrCreate(['user_id' => $user->id], ['cart_data' => $cart]);
        }

        // ------- If user id is in tepmorary reservation table, thriow error
        $tempReservationId = TemporaryReservation::where('user_id', $user->id)->get();

        // if user have a reservation throw error
        // we checking there, if temporary reservation instance exist
        // if reservationID is not null
        // *and temporary reservation ID is not equal to current reservation ID
        if ($tempReservationId->isNotEmpty() && $reservationId && $tempReservationId->first()->id !== $reservationId) {
            return response()->json(['message' => __('feedback.double_booking')], 403);
        }

        // TemporaryReservation::where('user_id', $user->id)->delete();

        // -------- Proceed with new reservation
        if (!$cabinNumber || !$cabinTypeId || !$cabinCategoryCode || !$cabinCapacity) {
            return response()->json(
                ['message' => 'Cabin number, type ID, capacity, and category ID are required.'],
                400,
            );
        }

        $filteredCabins = $this->filterCabins($cabinTypeId, null, null, true, $cabinCategoryCode, $cabinCapacity);

        if (isset($filteredCabins['error'])) {
            return response()->json(['message' => $filteredCabins['error']], $filteredCabins['status']);
        }

        $cabin = collect($filteredCabins['cabins'])->firstWhere('cabin_number', $cabinNumber);

        if (!$cabin) {
            return response()->json(['message' => __('feedback.cabin_not_available')], 404);
        }

        return $this->createTemporaryReservation($cabin, $user, $cart, 'clientSelect', $keepOldTimeStamp);
    }

    /*
  |--------------------------------------------------------------------------
  | Reserve a cabin type
  |--------------------------------------------------------------------------
  |
  |  Reserve a cabin type by customer service.
  |  User did not choose a cabin, so we will assign one.
  |
  */
    public function reserveType(Request $request) {
        $language = $request->input('language', 'en');
        App::setLocale($language);

        $cabinTypeId = $request->input('cabin_type_id');
        $cabinCategoryCode = $request->input('category_code');
        $cabinCapacity = $request->input('capacity');
        $isSwap = $request->input('is_swap', false);

        $user = Auth::user();
        $cart = $user ? Cart::where('user_id', $user->id)->first()?->cart_data ?? [] : [];

        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        // if cart is empty, return error
        if (empty($cart)) {
            return ErrorResponse::cartEmpty();
        }

        // ------- If user id is in tepmorary reservation table, thriow error
        $tempReservationId = TemporaryReservation::where('user_id', $user->id)->get();

        // if user have a reservation throw error
        if ($tempReservationId->isNotEmpty() && !$isSwap) {
            TemporaryReservation::where('user_id', $user->id)->delete();
            return response()->json(['message' => __('feedback.double_booking')], 403);
        }

        if (!$cabinTypeId || !$cabinCategoryCode || !$cabinCapacity) {
            return response()->json(['message' => 'Cabin category code, capacity and cabin type are required.'], 400);
        }

        // if is swap, release current reservation
        if ($isSwap) {
            $reservationService = new ReservationService();
            $reservationService->releaseCabin($user);

            $cart['choose_your_cabin'] = false;
            $cart['cabin_number'] = null;
            $cart['reservation_id'] = null;
            $cart['reservationTimestamp'] = null;
            Cart::updateOrCreate(['user_id' => $user->id], ['cart_data' => $cart]);
        }

        $filteredCabins = $this->filterCabins($cabinTypeId, null, null, true, $cabinCategoryCode, $cabinCapacity);

        if (isset($filteredCabins['error'])) {
            return response()->json(['message' => $filteredCabins['error']], $filteredCabins['status']);
        }

        $cabin = DB::transaction(function () use ($filteredCabins, $cabinTypeId) {
            $cabins = $filteredCabins['cabins'] ?? [];

            if (empty($cabins)) {
                return null;
            }

            // Shuffle cabins only for private cabins (type 1)
            $isPrivateCabin = $cabinTypeId == 1;
            $shuffledCabins = $isPrivateCabin ? Arr::shuffle($cabins) : $cabins;

            foreach ($shuffledCabins as $potentialCabin) {
                // Lock the cabin row itself to avoid race conditions
                $lockedCabin = Cabin::where('id', $potentialCabin['id'])
                    ->lockForUpdate()
                    ->first();

                // Return the cabin data if it was successfully locked
                if ($lockedCabin) {
                    return $potentialCabin;
                }
            }

            return null; // All candidates were already taken during lock attempt
        });

        if (!$cabin) {
            return response()->json(
                ['message' => 'There are no available cabins at the moment. Please try again later'],
                404,
            );
        }

        return $this->createTemporaryReservation($cabin, $user, $cart, 'serviceSelect', false);
    }

    /*
  |--------------------------------------------------------------------------
  | Release cabin
  |--------------------------------------------------------------------------
  |
  |  Relase logic as service, to use as separate endpoint or in CartController
  |
  */
    public function release(ReservationService $reservationService) {
        $user = Auth::user();

        if (!$user) {
            return ['status' => 404, 'message' => 'No cabin reserved'];
        }

        $response = $reservationService->releaseCabin($user);

        return response()->json(['message' => $response['message']], $response['status']);
    }

    /**
     * Helper method to create a temporary reservation.
     */
    protected function createTemporaryReservation($cabin, $user, $cart, $selectionType, $keepOldTimeStamp = null) {
        $reservationTime = (int) env('TEMPORARY_RESERVATION_TIME');

        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        try {
            DB::beginTransaction();

            // Only check for existing reservation if this is a private cabin (type 1)
            if (isset($cabin['cabin_type_id']) && $cabin['cabin_type_id'] == 1) {
                $existingReservation = TemporaryReservation::where('cabin_id', $cabin['id'])
                    ->where('expires_at', '>', now())
                    ->lockForUpdate()
                    ->first();
            } else {
                $existingReservation = null;
            }

            if ($existingReservation) {
                DB::rollBack();
                return response()->json(['message' => __('feedback.cabin_not_available')], 404);
            }

            $reserved = TemporaryReservation::create([
                'user_id' => $user->id,
                'cabin_id' => $cabin['id'],
                'cabin_number' => $cabin['cabin_number'],
                'expires_at' => $keepOldTimeStamp ? $keepOldTimeStamp : now()->addMinutes($reservationTime),
                'inventory' => 1,
            ]);

            $prevTimestamp = $keepOldTimeStamp ? $cart['reservation_timestamp'] : null;

            $cart['cabin_selection'] = $selectionType;
            $cart['reservation_id'] = $reserved->id;
            $cart['cabin_number'] = $selectionType === 'clientSelect' ? $cabin['cabin_number'] : null;
            $cart['cabin_category_type'] = $cabin['cabin_category_type'] ?? null;
            $cart['reservation_timestamp'] = $prevTimestamp ? $prevTimestamp : now()->timestamp;
            $cart['lower_bed_type_2'] = $selectionType === 'clientSelect' ? $cabin['lower_bed_type_2'] : null;

            \Log::info('Cart update after reservation:', ['cart' => $cart]);

            Cart::updateOrCreate(['user_id' => $user->id], ['cart_data' => $cart]);

            DB::commit();

            return response()->json(
                [
                    'success' => true,
                    'message' => 'Cabin reserved',
                    'reservation_id' => $reserved->id,
                    'time_to_cancel' => $reservationTime,
                    'updated_reservation' => $keepOldTimeStamp ? true : false,
                    'cabin_number' => $selectionType === 'clientSelect' ? $reserved->cabin_number : null,
                    'lower_bed_type_2' => $cabin['lower_bed_type_2'],
                ],
                200,
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Reservation error', [
                'error' => $e->getMessage(),
                'USER' => $user,
            ]);
            return response()->json(['message' => 'Reservation failed'], 500);
        }
    }
}
