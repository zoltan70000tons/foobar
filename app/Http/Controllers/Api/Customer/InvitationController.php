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
  public function index($bookingCode, $token)
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

    if (!$passengerInvitation || $passengerInvitation->email !== $user->email) {
      return response()->json(
        [
          'message' => 'Invalid token',
        ],
        400
      );
    }

    $booking = $passengerInvitation->booking;

    return response()->json([
      'booking' => $booking,
    ]);
  }
}
