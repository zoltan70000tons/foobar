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
      // get passenger from request
      $passenger = $request->user();

      // Check if passenger is authenticated and has the view-booking token
      if (!$passenger || !$request->user()->tokenCan('view-booking')) {
        return response()->json(['message' => 'Unauthorized'], 403);
      }

      // load related data
      $passenger->load(['fees', 'installments', 'payments']);

      // get booking details based on passenger's booking_id
      $booking = Booking::with(['cabin.category', 'cabin.cabinType', 'adjustments', 'event'])
        ->where('id', $passenger->booking_id)
        ->first();

      // Check if booking exists
      if (!$booking) {
        return response()->json(['message' => 'somethin went wrong'], 404);
      }

      // Set installment status attribute
      $passenger->setAttribute('installment_status', $passenger->installment_status);

      // return booking details, event details, and passenger details
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
  | Check Booking Logout
  |--------------------------------------------------------------------------
  |
  | Remove all users tokens and return a success message.
  */
  public function logout(Request $request)
  {
      $language = $request->input('language', 'en');
      App::setLocale($language);

      $accessToken = $request->bearerToken(); 

      if (!$accessToken) {
        return response()->json(['message' => 'Missing token'], 401);
      }

      // Parse token to get token record
      $tokenId = explode('|', $accessToken)[0];
      $token = PersonalAccessToken::find($tokenId);

      if (!$token || $token->abilities === null || !in_array('view-booking', $token->abilities)) {
          return response()->json(['message' => 'Unauthorized'], 403);
      }

      $passenger = $token->tokenable;

      // Delete all tokens for this passenger
      $passenger->tokens()->delete();

      // clean up custom token tracking table
      PassengerToken::where('passenger_id', $passenger->id)->delete();

      return response()->json(['message' => 'Logged out'], 200);
  }
}
