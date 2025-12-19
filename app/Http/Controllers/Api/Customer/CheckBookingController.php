<?php

namespace App\Http\Controllers\Api\Customer;

use App;
use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Passenger;
use App\Models\Event;
use Illuminate\Support\Str;
use App\Traits\StringNormalization;
use Illuminate\Support\Facades\Session;
use App\Models\PassengerToken;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Passport\Token as PassportToken;
use App\Http\Resources\EventResource;

class CheckBookingController extends Controller
{
  use StringNormalization;

  /*
  |--------------------------------------------------------------------------
  | Check Booking
  |--------------------------------------------------------------------------
  | 
  | 1. Retrieve booking based on token.
  | 2. Check if token is valid.
  | 3. Find booking based on token user.
  | 4. Return booking details.
  */
  public function getBooking(Request $request)
  {
    // get passenger from request
    $guestPassenger = $request->user();

    // Check if passenger is authenticated and has the view-booking token
    if (!$guestPassenger || !$request->user()->tokenCan('view-booking')) {
      return response()->json(['message' => 'Unauthorized'], 403);
    }

    // load related data
    $guestPassenger->load(['fees', 'installments', 'payments', 'onboardCredits']);

    // get booking details based on passenger's booking_id
    $booking = Booking::with([
      'cabin.category',
      'cabin.cabinType',
      'adjustments',
      'event',
      'passengers' => function ($q) {
        $q->with(['fees', 'installments', 'payments', 'onboardCredits']);
      },
    ])->find($guestPassenger->booking_id);

    // Check if booking exists
    if (!$booking) {
      return response()->json(['message' => 'Booking not found'], 404);
    }

    // Append installment status for all passengers in booking
    $booking->passengers->each->append('installment_status');

    // Set passenger masking info in request for Resource to use
    $request->attributes->set('passenger_masking', [
      'full_access_ids' => [$guestPassenger->id],
      'limited_fields' => [
        'id',
        'first_name',
        'dob',
        'last_name',
        'passenger_allocated_cost',
        'passenger_balance',
        'lead_passenger',
        'passenger_order',
      ],
      // here we pass param to use data masker for first_name with 'full' strategy
      'limited_sanitize' => [
        'first_name' => 'full',
        'last_name' => 'full',
        'dob' => 'dob',
      ],
    ]);

    // return booking details, event details, and passenger details
    return response()->json(
      [
        'booking' => new BookingResource($booking),
        'event' => new EventResource($booking->event),
      ],
      200
    );
  }

  /*
  |--------------------------------------------------------------------------
  | Check Booking Login
  |--------------------------------------------------------------------------
  |
  | 1. User sends email and booking code.
  | 2. Check if booking code exists.
  | 3. Check if the name & last name match any passenger in that booking.
  | 4. Generate a token
  */
  public function login(Request $request)
  {
    $language = $request->input('language', 'en');
    App::setLocale($language);

    $request->validate([
      'name' => 'required|string',
      'lastName' => 'required|string',
      'bookingCode' => 'required|string',
      'dateOfBirth' => 'required|string',
    ]);

    // Find booking by booking code
    $booking = Booking::where('booking_code', $request->bookingCode)->first();

    if (!$booking) {
      return response()->json(['message' => __('feedback.booking_not_found')], 404);
    }

    // Ensure booking event is in valid status
    if (!in_array($booking->event->status, ['PUBLIC', 'PRE-SALE'])) {
      return response()->json(['message' => __('feedback.booking_not_found')], 404);
    }

    // Normalize input names
    $formattedName = $this->normalizeString($request->name);
    $formattedLastName = $this->normalizeString($request->lastName);

    // Fetch all passengers for this booking
    $passengers = Passenger::where('booking_id', $booking->id)
      ->where('dob', $request->dateOfBirth)
      ->get();

    // Find matching passenger
    $matchedPassenger = $passengers->first(
      fn($p) => $this->isSimilar($this->normalizeString($p->first_name ?? ''), $formattedName) &&
        $this->isSimilar($this->normalizeString($p->last_name ?? ''), $formattedLastName)
    );

    if (!$matchedPassenger) {
      return response()->json(['message' => 'Passenger not found'], 404);
    }

    // Create a short-lived personal access token scoped to view booking (15 minutes)
    try {
      $tokenResult = $matchedPassenger->createToken('check-booking', ['view-booking']);
      $tokenModel = $tokenResult->token; // Laravel Passport Token model
      $expiresAt = Carbon::now()->addMinutes(15);
      $tokenModel->expires_at = $expiresAt;
      $tokenModel->save();

      // Track token (optional, for cleanup or auditing)
      PassengerToken::create([
        'passenger_id' => $matchedPassenger->id,
        'token_id' => $tokenModel->id,
        'expires_at' => $expiresAt,
      ]);

      return response()->json([
        'token' => $tokenResult->accessToken,
        'expires_at' => $expiresAt,
        'scope' => ['view-booking'],
      ]);
    } catch (\Throwable $e) {
      // Surface a JSON error instead of HTML exception page
      return response()->json(
        [
          'message' => 'Unable to issue access token',
          'error' => $e->getMessage(),
        ],
        500
      );
    }
  }

  /*
  |--------------------------------------------------------------------------
  | Check Booking Logout
  |--------------------------------------------------------------------------
  |
  | Remove all users tokens and return a success message.
  */
  public function logout(Request $request)
  {
    $language = $request->input('language', 'en');
    App::setLocale($language);

    // Requires auth:passenger middleware; retrieves authenticated passenger and current token
    $passenger = $request->user();
    if (!$passenger) {
      return response()->json(['message' => 'Unauthorized'], 401);
    }

    // Revoke all tokens for this passenger (scoped to this provider)
    $provider = $passenger->getProviderName();

    $tokens = PassportToken::where('user_id', $passenger->getAuthIdentifier())
      ->whereHas('client', function (Builder $query) use ($provider) {
        $query->where(function (Builder $query) use ($provider) {
          if ($provider === config('auth.guards.api.provider')) {
            $query->orWhereNull('provider');
          }
          $query->orWhere('provider', $provider);
        });
      })
      ->with('refreshToken')
      ->get();

    foreach ($tokens as $token) {
      $token->refreshToken?->revoke();
      $token->revoke();
    }

    // Clean up custom token tracking table for this passenger
    PassengerToken::where('passenger_id', $passenger->id)->delete();

    return response()->json(['message' => 'Logged out from all sessions'], 200);
  }
}
