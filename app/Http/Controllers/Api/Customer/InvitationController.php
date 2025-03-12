<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\StorePassengerRequest;

use App\Repositories\BookingRepository;
use App\Repositories\CustomerBookingRepository;
use App\Services\CustomerBookingService;
use App\Helpers\PriceCalculation;
use App\Interfaces\PassengerInterface;
use App\Models\Adjustment;
use App\Models\CabinType;
use App\Models\Event;
use App\Models\PassengerInvitation;
use App\Mail\CustomerConfirmationBooking;
use App\Models\Passenger;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

class InvitationController extends Controller
{
  protected $bookingRepository;
  protected $customerBookingService;
  protected $customerBookingRepository;

  public function __construct(
    BookingRepository $bookingRepository,
    CustomerBookingService $CustomerBookingService,
    CustomerBookingRepository $customerBookingRepository
  ) {
    $this->bookingRepository = $bookingRepository;
    $this->customerBookingService = $CustomerBookingService;
    $this->customerBookingRepository = $customerBookingRepository;
  }

  /*
  |--------------------------------------------------------------------------
  |  Show booking by booking code
  |--------------------------------------------------------------------------
  |
  |  Return booking related to passenger invitation token
  |
  */
  public function index(int $eventId, string $bookingCode, string $token)
  {
    $user = Auth::user();

    if (!$bookingCode && !$token) {
      return response()->json(
        [
          'message' => 'Booking code or token is required',
        ],
        400
      );
    }

    $passengerInvitation = PassengerInvitation::with(
      'booking',
      'booking.event',
      'booking.cabin.category',
      'booking.cabin.cabinType'
    )
      ->where('token', $token)
      ->first();

    // check the eventId is the same as the booking event id
    if ($passengerInvitation->booking->event_id !== $eventId) {
      return response()->json(
        [
          'message' => 'Invalid token',
        ],
        400
      );
    }

    if (!$passengerInvitation || $passengerInvitation->email !== $user->email) {
      return response()->json(
        [
          'message' => 'Invalid token',
        ],
        400
      );
    }

    $booking = $passengerInvitation->booking;
    $invitedBy = $booking->passengers()->where('lead_passenger', true)->first();

    return response()->json([
      'booking' => $booking,
      'invited_by' => $invitedBy->email,
    ]);
  }

  /*
  |--------------------------------------------------------------------------
  |  Add passenger to booking
  |--------------------------------------------------------------------------
  |
  |  Add passenger via invitation token
  |
  */
  public function addPax(StorePassengerRequest $request, int $eventId, string $bookingCode, string $token)
  {
    $validated = $request->validated();
    // create passenger with booking id
    $result = $this->customerBookingRepository->addPassengerWithToken($eventId, $bookingCode, $validated, $token);

    return $result;
    //
  }

  /*
  |--------------------------------------------------------------------------
  |  Remove invitation
  |--------------------------------------------------------------------------
  |
  |  Remove invitation via invitation token
  |
  */
  public function removeInvitation(int $eventId, string $bookingCode, string $token)
  {
    $user = Auth::user();

    if (!$bookingCode && !$token && !$eventId) {
      return response()->json(
        [
          'message' => 'Booking code or token is required',
        ],
        400
      );
    }

    $passengerInvitation = PassengerInvitation::where('token', $token)
      ->where('email', $user->email)
      ->first();

    if (!$passengerInvitation) {
      return response()->json(
        [
          'message' => 'Invalid token or user data',
        ],
        400
      );
    }

    $passengerInvitation->delete();

    return response()->json([
      'message' => 'Invitation removed',
    ]);
  }
}
