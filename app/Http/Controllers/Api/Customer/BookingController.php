<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\StorePassengerRequest;
use Illuminate\Support\Facades\Auth;
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

class BookingController extends Controller
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
  |  Store new booking
  |--------------------------------------------------------------------------
  |
  |  Store a new booking
  |
  */
  public function store(StoreBookingRequest $request)
  {
    $validated = $request->validated();

    // Get authenticated user
    $user = Auth::user();
    $cart = $request->session()->get('cart', []);

    if (!$user) {
      return response()->json(['message' => 'Unauthorized'], 403);
    }

    if (!$cart) {
      return response()->json(['message' => 'Cart is empty'], 400);
    }

    \Log::info('cart', $cart);

    try {
      $reservationId = $validated['cart']['reservation_id'];
      $eventId = (int) $validated['cart']['event_id'];
      $paymentPlan = $validated['cart']['payment_plan'];
      $numberOfInstallments = $paymentPlan === 'INSTALLMENTS' ? $validated['cart']['number_of_installments'] : null;

      // Process booking data
      $bookingData = [
        'event_id' => $eventId,
        'customer_id' => $user->id,
        'payment_plan' => $paymentPlan,
        'number_of_installments' => $numberOfInstallments ? $numberOfInstallments : 1,
        'completed' => false,
        'is_cancelled' => false,
        'is_single_occupancy' => false,
        'tags' => json_encode(['New']),
      ];

      // get price from session
      // $price = $validated["cart"]["price_total"];
      $adjustments = Adjustment::where('event_id', $eventId)->first();
      $eventStatus = Event::find($eventId)->status;
      $priceCalc = PriceCalculation::calculatePricePerPassenger([
        'cabinPrice' => $validated['cart']['cabin_price'],
        'cabinCapacity' => $validated['cart']['cabin_capacity'],
        'cabinType' => $cart['cabin_type'] === 'private-cabin' ? true : false,
        'selectedAdjustments' => $cart['addons'],
        'adjustments' => $adjustments,
        'eventStatus' => $eventStatus,
      ]);

      $totalPassenger = $priceCalc['totalPassenger'];

      // Process passenger data
      $passengerData = [
        'confirmed_booking_email' => false,
        'lead_passenger' => $validated['cart']['cabin_type'] === 'private-cabin' ? true : false,
        'payment_method' => $validated['paymentMethod'],
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
        'special_options' => $validated['specialOptions'],
        'special_request' => $validated['specialRequest'] ?? null,
        'newsletter' => $validated['newsletter'],
        'travel_info' => $validated['travelInfo'],
        'hear_about' => $validated['info'],
        'referral_details' => $validated['referralDetails'],
        'terms_n_cons' => $validated['terms'],
        'cabin_conf_accp' => $validated['cart']['cabin_conf_accp'],
        'single_t_agreement' => $validated['cart']['single_t_agreement'],
        // passenger allocated cost - take from calculation
        'passenger_allocated_cost' => $totalPassenger,
        'addons' => $validated['cart']['addons'],
        'passenger_balance' => 0,
        'was_on_board' => false,
      ];

      // Call to booking repository method
      $result = $this->bookingRepository->createBooking($bookingData, $passengerData, null, $reservationId);

      // Send confirmation email
      //\Log::info('Booking created successfully', $result);

      // Delete current sesion
      $request->session()->forget('cart');
      $request->session()->forget('reservation_id');

      \Log::info('Booking created successfully', $result);

      $bookingCode = $result['booking']['booking_code'];
      $passengerEmail = $passengerData['email'];

      \Log::info('Sending confirmation email for booking code: ' . $bookingCode . ' to email: ' . $passengerEmail);

      if (!$bookingCode || !$passengerEmail) {
        return response()->json(
          [
            'message' => 'Booking created successfully, but failed to send confirmation email.',
            'booking' => [
              'booking_request_id' => $result['booking']['booking_request_id'],
            ],
          ],
          201
        );
      }

      // Send confirmation email
      $this->sendConfirmationEmail($bookingCode, $passengerEmail, $cart, 'en');

      return response()->json(
        [
          'message' => 'Booking created successfully.',
          'booking' => [
            'booking_request_id' => $result['booking']['booking_request_id'],
          ],
          // "passenger" => $result["passenger"],
        ],
        201
      );
    } catch (\Exception $e) {
      return response()->json(
        [
          'message' => 'An error occurred while creating the booking.',
          'error' => $e->getMessage(),
        ],
        500
      );
    }
  }

  /*
  |--------------------------------------------------------------------------
  | Send email confirmation
  |--------------------------------------------------------------------------
  |
  |  This method trigger the email confirmation
  |
  */
  private function sendConfirmationEmail(
    string $bookingCode,
    string $passengerEmail,
    array $cart,
    string $language
  ): void {
    try {
      // Get booking data with relationships
      $booking = Booking::where('booking_code', $bookingCode)
        ->with(['cabin.category', 'passengers.installments', 'adjustments'])
        ->first();

      // Convert booking to array for logging
      \Log::info('EMAIL Booking data', ['booking' => $booking->toArray()]);

      $cabinType = CabinType::find($booking->cabin->cabin_type_id)->cabin_type;

      // if payment installments attach the payment plan
      $installments = [];

      if ($booking->payment_plan === 'INSTALLMENTS') {
        // Get passenger_id from booking
        $passenger = $booking->passengers()->first();
        // Get installments
        $passInstallments = $passenger->installments; // FIXED: Use $passenger, not $booking->passengers->installments

        // Convert installments to array for logging
        \Log::info('EMAIL passInstallments data', ['installments 2' => $passInstallments->toArray()]);

        // Count installments properly
        $installmentCount = $passInstallments->count();

        if ($installmentCount > 0) {
          $totalPrice = $passenger->passenger_allocated_cost;
          $totalPriceDivided = floatval($totalPrice) / $installmentCount;

          // Map to array
          $installments = $passInstallments
            ->map(function ($installment) use ($totalPriceDivided) {
              return [
                'due_date' => $installment->due_date,
                'amount' => round($totalPriceDivided, 2),
              ];
            })
            ->toArray();
        }

        // Convert installments to array for logging
        \Log::info('EMAIL installments data', ['installments 3' => (array) $installments]);
      }

      Mail::to($passengerEmail)->send(
        new CustomerConfirmationBooking($booking, $cabinType, $installments, $cart, $language)
      );
    } catch (\Exception $e) {
      \Log::error('Failed to send booking confirmation email: ' . $e->getMessage());
    }
  }

  /*
  |--------------------------------------------------------------------------
  |  Get booking confirmation
  |--------------------------------------------------------------------------
  |
  |  This method is used to confirm the booking it's out 
  |
  */
  // public function bookingConfirmation($bookingCode)
  // {
  //   $user = Auth::user();

  //   if (!$user) {
  //     return response()->json(['message' => 'Unauthorized'], 403);
  //   }

  //   $result = $this->customerBookingRepository->getBookingByCode($bookingCode, $user);

  //   if (!$result) {
  //     return response()->json(['message' => 'Booking not found'], 404);
  //   }

  //   return response()->json([
  //     'status' => 'success',
  //   ]);
  // }

  /*
  |--------------------------------------------------------------------------
  |  Get booking by code
  |--------------------------------------------------------------------------
  |
  |  This method return single booking by code related to the user
  |
  */
  public function singleBooking($bookingCode)
  {
    $user = Auth::user();

    if (!$user) {
      return response()->json(['message' => 'Unauthorized'], 403);
    }

    $result = $this->customerBookingRepository->getBookingByCode($bookingCode, $user);

    if (!$result) {
      return response()->json(['message' => 'Booking not found'], 404);
    }

    // hide cabin number if status is new or cancelled
    if ($result->status === 'NEW' || $result->status === 'CANCELLED') {
      $result->cabin->makeHidden(['cabin_number']);
      $result->cabin->cabinSpec->makeHidden(['cabin_number']);
    }

    // hide
    $result->passengers->map(function ($passenger) {
      $passenger->payments->map(function ($payment) {
        $payment->makeHidden(['BIP_ID']);
        $payment->makeHidden(['notes']);
      });
    });

    $schema = [
      'booking' => $result,
      // 'available_seats' => $available_seats,
    ];

    return response()->json($schema);
  }

  /*
  |--------------------------------------------------------------------------
  |  Get all bokings
  |--------------------------------------------------------------------------
  |
  |  This method return all bokings related to the user
  |
  */
  public function allBookings()
  {
    $user = Auth::user();

    if (!$user) {
      return response()->json(['message' => 'Unauthorized'], 403);
    }

    $result = $this->customerBookingService->getMyBookings($user);

    return response()->json([
      'bookings' => $result,
    ]);
  }

  /*
  |--------------------------------------------------------------------------
  | Set empty seat
  |--------------------------------------------------------------------------
  |
  |  This method call the service to set an empty seat in the booking
  |
  */
  public function emptySeat(Request $request, $bookingCode)
  {
    $user = Auth::user();

    // no user
    if (!$user) {
      return response()->json(['message' => 'Unauthorized'], 403);
    }

    $booking = Booking::where('booking_code', $bookingCode)->first();

    // no booking
    if (!$booking) {
      return response()->json(['message' => 'Booking not found'], 404);
    }

    if ($booking->is_single_occupancy) {
      return response()->json(['message' => 'This booking is single occupancy'], 400);
    }

    // not the owner
    if ($booking->customer_id !== $user->id) {
      return response()->json(['message' => 'Unauthorized'], 403);
    }

    $passengerOrder = $request->input('passenger_order');

    $result = $this->customerBookingRepository->setEmptySeat($bookingCode, $passengerOrder);

    return $result;
  }

  /*
  |--------------------------------------------------------------------------
  | Add passenger manually
  |--------------------------------------------------------------------------
  |
  |  This method call the service to add a passenger to a booking manually
  |
  */
  public function addPassenger(StorePassengerRequest $request, $bookingCode)
  {
    $user = Auth::user();

    if (!$user) {
      return response()->json(['message' => 'Unauthorized'], 403);
    }

    $booking = Booking::where('booking_code', $bookingCode)->first();

    if (!$booking) {
      return response()->json(['message' => 'Booking not found'], 404);
    }

    if ($booking->is_single_occupancy) {
      return response()->json(['message' => 'This booking is single occupancy'], 400);
    }

    $validated = $request->validated();

    $passengerOrder = $request->input('passenger_order');

    // Check if passenger order is already taken
    $passenger = $booking->passengers()->where('passenger_order', $passengerOrder)->first();
    if ($passenger->email) {
      return response()->json(['message' => 'Passenger order is already taken'], 400);
    }

    // create passenger with booking id
    $result = $this->customerBookingRepository->addPassengerManually($bookingCode, $passenger, $validated);

    return $result;
  }

  /*
  |--------------------------------------------------------------------------
  | Add passenger via email
  |--------------------------------------------------------------------------
  |
  |  This method call the service to add a passenger to a booking via email
  |
  */
  public function addPassengerViaEmail(Request $request, $bookingCode)
  {
    $user = Auth::user();

    if (!$user) {
      return response()->json(['message' => 'Unauthorized'], 403);
    }

    $booking = Booking::where('booking_code', $bookingCode)->first();

    if (!$booking) {
      return response()->json(['message' => 'Booking not found'], 404);
    }

    if ($booking->is_single_occupancy) {
      return response()->json(['message' => 'This booking is single occupancy'], 400);
    }

    $passengerOrder = $request->input('passenger_order');
    // get email and survivor number from request
    $email = $request->input('email');

    if (!$email) {
      return response()->json(['message' => 'Email and survivor number are required'], 400);
    }

    // if booking have passenger with this email or passenger_order is taken
    // Check if passenger already exists with this email
    $existingPassenger = $booking->passengers()->where('email', $email)->first();
    if ($existingPassenger) {
      return response()->json(['message' => 'A passenger with this email is already added to the booking'], 400);
    }

    // Check if passenger order is already taken
    $passenger = $booking->passengers()->where('passenger_order', $passengerOrder)->first();
    if ($passenger->email) {
      return response()->json(['message' => 'Passenger order is already taken'], 400);
    }

    // Check if an invitation already exists for this email and booking
    $passengerInvitation = PassengerInvitation::where('booking_id', $booking->id)
      ->where('email', $email)
      ->first();

    if ($passengerInvitation) {
      return response()->json(['message' => 'Passenger with this email has already been invited'], 400);
    }

    $passengerSlotId = PassengerInvitation::where('booking_id', $booking->id)
      ->where('passenger_id', $passenger->id)
      ->first();

    if ($passengerSlotId) {
      return response()->json(['message' => 'Passenger with this slot has already been taken'], 400);
    }

    $result = $this->customerBookingService->addPassengerViaEmail($booking, $passenger, $email);

    return $result;
  }

  /*
  |--------------------------------------------------------------------------
  | Add Pax
  |--------------------------------------------------------------------------
  |
  |  This method is checking the signed URL and returning the booking
  |
  */
  public function validateAddPassenger(Request $request)
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
    $booking = Booking::where('id', $passengerInvitation->booking_id)->first();

    if (!$booking) {
      return response()->json(['message' => 'Booking not found'], 404);
    }

    // You can add any additional logic here, such as checking if the passenger can be added
    return response()->json([
      'message' => 'Valid URL',
      'booking' => $booking,
    ]);
  }

  /*
  |--------------------------------------------------------------------------
  | Submit booking via email
  |--------------------------------------------------------------------------
  |
  */
  public function submitAddPassenger(StorePassengerRequest $request, $bookingCode, $token)
  {
    $validated = $request->validated();
    // create passenger with booking id
    $result = $this->customerBookingRepository->addPassengerNonAuth($bookingCode, $validated, $token);

    return $result;
  }

  /*
  |--------------------------------------------------------------------------
  | Cancel invitation
  |--------------------------------------------------------------------------
  |
  |  Delete the invitation row
  |
  */
  public function cancelInvitation(Request $request, $bookingCode)
  {
    $user = Auth::user();

    if (!$user) {
      return response()->json(['message' => 'Unauthorized'], 403);
    }

    $booking = Booking::where('booking_code', $bookingCode)->first();

    if (!$booking) {
      return response()->json(['message' => 'Booking not found'], 404);
    }

    // passenger_id: passengerId,
    // passenger_inivtation_id: invitationId,
    // passenger_inivtation_token: token,
    $passengerId = $request->input('passenger_id');
    $invitationId = $request->input('passenger_inivtation_id');
    $token = $request->input('passenger_inivtation_token');

    if (!$passengerId || !$invitationId || !$token) {
      return response()->json(['message' => 'Passenger ID, Invitation ID, and Token are required'], 400);
    }

    $passengerInvitation = PassengerInvitation::where('id', $invitationId)
      ->where('booking_id', $booking->id)
      ->where('passenger_id', $passengerId)
      ->where('token', $token)
      ->first();

    if (!$passengerInvitation) {
      return response()->json(['message' => 'Invitation not found'], 404);
    }

    $passengerInvitation->delete();

    return response()->json(['message' => 'Invitation cancelled'], 200);
  }
}
