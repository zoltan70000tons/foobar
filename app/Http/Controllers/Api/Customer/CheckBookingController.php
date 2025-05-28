<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Passenger;
use App\Models\Event;
use Illuminate\Support\Str;
use App\Traits\StringNormalization;
use Illuminate\Support\Facades\Session;

class CheckBookingController extends Controller
{
  use StringNormalization;

  /*
  |--------------------------------------------------------------------------
  | Check Booking
  |--------------------------------------------------------------------------
  | 
  | 1. Retrieve booking token from session or cookie.
  | 2. Check if token is valid.
  | 3. Find booking based on token.
  | 4. Return booking details.
  */
  public function getBooking(Request $request)
  {
    // Retrieve token from session or cookie
    $bookingToken = Session::get('booking_token') ?? $request->cookie('booking_token');

    if (!$bookingToken) {
      return response()->json(['message' => 'No valid session found'], 401);
    }

    // Check if session has stored booking data
    if (!Session::has('booking_data')) {
      return response()->json(['message' => 'Session expired or no booking data found'], 401);
    }

    // Return stored booking data
    return response()->json(Session::get('booking_data'));
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
    $request->validate([
      'name' => 'required|string',
      'lastName' => 'required|string',
      'bookingCode' => 'required|string',
      'dateOfBirth' => 'required|string',
    ]);

    // Generate token if not exists
    if (!Session::has('booking_token')) {
      $token = Str::uuid()->toString();
      Session::put('booking_token', $token);
      Session::put('booking_token_expires', now()->addHours(24));
    }

    $bookingToken = Session::get('booking_token');
    $expiresAt = Session::get('booking_token_expires');

    // If the session is expired, reset session
    if (now()->greaterThan($expiresAt)) {
      Session::flush(); // Clear session completely
      return $this->login($request);
    }

    // Find booking by booking code
    $booking = Booking::with('cabin.category', 'cabin.cabinType', 'adjustments')
      ->where('booking_code', $request->bookingCode)
      ->first();

    if (!$booking) {
      return response()->json(['message' => 'Booking not found'], 404);
    }

    // Ensure booking event is in valid status
    if (!in_array($booking->event->status, ['PUBLIC', 'PRE-SALE'])) {
      return response()->json(['message' => 'Booking not found'], 404);
    }

    // Normalize input names
    $formattedName = $this->normalizeString($request->name);
    $formattedLastName = $this->normalizeString($request->lastName);

    // Fetch all passengers for this booking
    $passengers = Passenger::with('fees', 'installments')
      ->where('booking_id', $booking->id)
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

    // Set installment status attribute
    $matchedPassenger->setAttribute('installment_status', $matchedPassenger->installment_status);


    // Store booking details in session instead of querying database again
    Session::put('booking_data', [
      'booking' => $booking,
      'event' => Event::find($booking->event_id),
      'passengers' => $matchedPassenger,
    ]);

    return response()
      ->json([
        'booking' => $booking,
        'event' => Event::find($booking->event_id),
        'passengers' => $matchedPassenger,
        'token' => $bookingToken,
      ])
      ->cookie('booking_token', $bookingToken, 1440); // Store token in cookie
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
    $user = $request->user();

    // Check if user has an active booking
    $hasActiveBooking = $user->bookings()->whereIn('status', ['NEW', 'ON HOLD'])->exists();

    return response()->json([
        'canDelete' => !$hasActiveBooking,
        'message' => $hasActiveBooking
            ? 'You cannot delete your account while you have an active booking.'
            : 'You can delete your account.',
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
    Session::forget(['booking_token', 'booking_token_expires', 'booking_data']);
    return response()->json(['message' => 'Logged out']);
  }
}
