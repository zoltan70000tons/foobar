<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Passenger;
use App\Models\Event;
use Illuminate\Support\Str;

class AddPaxController extends Controller
{
  /**
   * 1. User sends email and booking code.
   * 2. Check if booking code exists.
   * 3. Check if the name & last name match any passenger in that booking.
   * 4. Return the booking details.
   */
  public function show(Request $request)
  {
    $request->validate([
      'name' => 'required|string',
      'lastName' => 'required|string',
      'bookingCode' => 'required|string',
      'dateOfBirth' => 'required|string',
    ]);

    // Find booking by booking code
    $booking = Booking::where('booking_code', $request->bookingCode)->first();

    if (!$booking) {
      return response()->json(['message' => 'Booking not found'], 404);
    }

    // if booking status is not public or pre-sale, return error
    if (!in_array($booking->event->status, ['PUBLIC', 'PRE-SALE'])) {
      return response()->json(['message' => 'Booking not found'], 404);
    }

    $dateOfBirth = $request->dateOfBirth;
    // Normalize input names
    $formattedName = $this->normalizeString($request->name);
    $formattedLastName = $this->normalizeString($request->lastName);

    // Fetch all passengers for this booking
    $passengers = Passenger::where('booking_id', $booking->id)
      ->where('dob', $dateOfBirth)
      ->get();

    // Try to find a passenger with a similar name
    $matchedPassenger = $passengers->first(function ($passenger) use ($formattedName, $formattedLastName) {
      return $this->isSimilar($this->normalizeString($passenger->first_name ?? ''), $formattedName) &&
        $this->isSimilar($this->normalizeString($passenger->last_name ?? ''), $formattedLastName);
    });

    if (!$matchedPassenger) {
      return response()->json(['message' => 'Passenger not found'], 404);
    }

    // Fetch event related to booking
    $event = Event::find($booking->event_id);

    return response()->json([
      'booking' => [
        'booking_code' => $booking->booking_code,
        'event' => $event,
        'passengers' => $matchedPassenger,
      ],
    ]);
  }

  /**
   * Normalize a string by removing special characters and converting to uppercase.
   */
  private function normalizeString(string $string): string
  {
    return strtoupper(trim(Str::ascii($string))); // Remove accents and normalize casing
  }

  /**
   * Check if two strings are similar based on Levenshtein distance and Soundex.
   */
  private function isSimilar(string $input, string $stored): bool
  {
    // Direct match
    if ($input === $stored) {
      return true;
    }

    // Check Soundex (similar pronunciation)
    if (soundex($input) === soundex($stored)) {
      return true;
    }

    // Allow minor typos with Levenshtein distance (threshold: 2)
    return levenshtein($input, $stored) <= 2;
  }
}
