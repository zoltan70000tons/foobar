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
use Illuminate\Support\Facades\Auth;

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
    // if user is auth response error
    $user = Auth::check();
    if ($user) {
      return response()->json(['message' => 'User is authenticated'], 403);
    }

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
    // if user is auth response error
    $user = Auth::check();

    if ($user) {
      return response()->json(['message' => 'User is authenticated'], 403);
    }

    $validated = $request->validated();

    // if in validated survivor number exist, return error
    if (isset($validated['survivor_number'])) {
      return response()->json(['message' => 'Survivor number is not allowed'], 400);
    }

    $dataToUpdate = [
      'survivor_number' => null,
      'first_name' => $validated['firstName'],
      'middle_name' => $validated['middleName'] ?? null,
      'last_name' => $validated['lastName'],
      'dob' => $validated['dateOfBirth'],
      'gender' => $validated['gender'],
      'citizenship' => $validated['citizenship'],
      'address_first' => $validated['addressLine1'],
      'address_second' => $validated['addressLine2'],
      'city' => $validated['city'],
      'state' => $validated['state'],
      'postal_code' => $validated['zipCode'],
      'country' => $validated['country'],
      'email' => $validated['email'],
      'phone' => $validated['phoneNumber'],
      'emergency_c_name' => $validated['emergencyContactName'],
      'emergency_c_phone' => $validated['emergencyPhoneNumber'],
      'special_request' => $validated['specialRequest'],
    ];

    // create passenger with booking id
    $result = $this->customerBookingRepository->addPassengerWithToken($eventId, $bookingCode, $token, $dataToUpdate);

    return $result;
  }
}
