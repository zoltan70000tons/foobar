<?php

namespace App\Http\Controllers\Api\Customer;

use App;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Passenger;
use App\Models\Event;
use Illuminate\Support\Str;
use App\Traits\StringNormalization;
use Illuminate\Support\Facades\Session;
use App\Models\PassengerToken;
use Laravel\Sanctum\PersonalAccessToken;


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

      $passenger = $request->user();

      if (!$passenger || !$request->user()->tokenCan('view-booking')) {
          return response()->json(['message' => 'Unauthorized'], 403);
      }

      $passenger->load(['fees', 'installments', 'payments']);

      $booking = Booking::with('cabin.category', 'cabin.cabinType', 'adjustments')
        ->where('id', $passenger->booking_id)
        ->first();

      if (!$booking) {
        return response()->json(['message' => 'somethin went wrong'], 404);
      }

      return response()->json([
        'booking' => $booking,
        'event' => Event::find($booking->event_id),
        'passengers' => $passenger,
      ], 200);

  }

  /*
  |--------------------------------------------------------------------------
  | Check Booking Login
  |--------------------------------------------------------------------------
  |
  | 1. User sends email and booking code.
  | 2. Check if booking code exists.
  | 3. Check if the name & last name match any passenger in that booking.
  | 4. Return the booking details.
  | 5. Generate a token with session.
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
    $booking = Booking::with('cabin.category', 'cabin.cabinType', 'adjustments')
      ->where('booking_code', $request->bookingCode)
      ->first();

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

      // Create Sanctum token (valid for 24 hours)
      $token = $matchedPassenger->createToken('guest-booking', ['view-booking']);
      $expiresAt = now()->addHours(24);

      // Track token (optional, for cleanup or auditing)
      PassengerToken::create([
          'passenger_id' => $matchedPassenger->id,
          'token_id' => $token->accessToken->id,
          'expires_at' => $expiresAt,
      ]);

      return response()->json([
        'token' => $token->plainTextToken,
        'expires_at' => $expiresAt,
      ]);

  }

  /*
  |--------------------------------------------------------------------------
  | Check user cant delete account
  |--------------------------------------------------------------------------
  |
  | User cannot delete account if they have an active booking.
  */
  public function canDeleteAccount(Request $request)
  {
    $language = $request->input('language', 'en');
    App::setLocale($language);
    $user = $request->user();

    // Check if user has an active booking
    $hasActiveBooking = $user->bookings()->whereIn('status', ['NEW', 'ON HOLD'])->exists();

    return response()->json([
      'canDelete' => !$hasActiveBooking,
      'message' => $hasActiveBooking
        ? __('feedback.cannot_delete_account')
        : __('feedback.proceed_delete_account'),
    ]);
  }

  /*
  |--------------------------------------------------------------------------
  | Check Booking Logout
  |--------------------------------------------------------------------------
  |
  | 1. Remove booking token from session.
  | 2. Remove booking data from session.
  */
  public function logout(Request $request)
  {

    $language = $request->input('language', 'en');
    App::setLocale($language);

    $bearerToken = $request->bearerToken();

    if (!$bearerToken) {
        return response()->json(['message' => 'No token provided'], 401);
    }

    $accessToken = PersonalAccessToken::findToken($bearerToken);

    if (!$accessToken || $accessToken->tokenable_type !== Passenger::class) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // Delete the token (only)
    $accessToken->delete();

    // Optionally clean up custom token tracking
    PassengerToken::where('token_id', $accessToken->id)->delete();

    return response()->json(['message' => 'Logged out']);
  }
}
