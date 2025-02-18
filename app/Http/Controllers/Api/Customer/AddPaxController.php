<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Passenger;
use App\Models\Event;

class AddPaxController extends Controller
{
  /**
   * 1. User send email and booking code.
   * 2. Check if booking code exist.
   * 3. Check the email is same as passenger with booking code.
   * 4. Return the booking details.
   */
  public function show(Request $request)
  {
    $request->validate([
      'name' => 'required|string',
      'last_name' => 'required|string',
      'bookingCode' => 'required|string',
    ]);

    $booking = Booking::where('booking_code', $request->bookingCode)->first();

    if (!$booking) {
      return response()->json(['message' => 'Booking not found'], 404);
    }

    // Capitalize first letter of each word (to match DB format)
    $formattedName = ucfirst(strtolower($request->name));
    $formattedLastName = ucfirst(strtolower($request->last_name));

    $passenger = Passenger::where('first_name', $formattedName)
      ->where('last_name', $formattedLastName)
      ->where('booking_id', $booking->id)
      ->first();

    if (!$passenger) {
      return response()->json(['message' => 'Passenger not found'], 404);
    }

    $event = Event::find($booking->event_id);

    // schema to return
    $booking = [
      'booking_code' => $booking->booking_code,
      'event' => $event,
      'passengers' => $passenger,
    ];

    return response()->json([
      'booking' => $booking,
    ]);
  }
}
