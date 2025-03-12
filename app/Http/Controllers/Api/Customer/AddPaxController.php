<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Passenger;
use App\Models\Event;
use Illuminate\Support\Str;
use App\Models\PassengerInvitation;
use App\Http\Requests\StorePassengerRequest;
use App\Repositories\CustomerBookingRepository;

class AddPaxController extends Controller
{
  protected $customerBookingRepository;

  public function __construct(CustomerBookingRepository $customerBookingRepository)
  {
    $this->customerBookingRepository = $customerBookingRepository;
  }

  /*
  |--------------------------------------------------------------------------
  | Validate Add Passenger
  |--------------------------------------------------------------------------
  |
  |  In this method we validate the request for adding a passenger
  |
  */
  public function validate(Request $request)
  {
    // Validate the signed URL
    if (!$request->hasValidSignature(false)) {
      return response()->json(['message' => 'Invalid or expired URL'], 403);
    }

    $token = $request->input('token');
    //$bookingCode = $request->input('bookingCode');

    // check the invitation exist
    $passengerInvitation = PassengerInvitation::where('token', $token)->first();

    \Log::info('passengerInvitation', ['pass invi' => $passengerInvitation]);

    if (!$passengerInvitation) {
      return response()->json(['message' => 'Invitation not found'], 404);
    }

    // Fetch the booking and check if it exists
    $booking = Booking::with('event', 'cabin.category', 'cabin.cabinType')
      ->where('id', $passengerInvitation->booking_id)
      ->first();

    if (!$booking) {
      return response()->json(['message' => 'Booking not found'], 404);
    }

    // unset agent_id
    unset($booking->agent_id);

    // You can add any additional logic here, such as checking if the passenger can be added
    return response()->json([
      'message' => 'Valid URL',
      'booking' => $booking,
    ]);
  }

  /*
  |--------------------------------------------------------------------------
  | Store Add Passenger
  |--------------------------------------------------------------------------
  |
  |  In this method we store the passenger details
  |
  */
  public function store(StorePassengerRequest $request, int $eventId, string $bookingCode, string $token)
  {
    $validated = $request->validated();
    // create passenger with booking id
    $result = $this->customerBookingRepository->addPassengerWithToken($eventId, $bookingCode, $validated, $token);

    return $result;
  }
}
