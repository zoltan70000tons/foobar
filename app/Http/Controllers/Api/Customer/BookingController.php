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
use App\Models\Adjustment;

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

  /**
   * Store a new booking
   *
   * Validate the request and use BookingRepository to create a new booking
   *
   * @param StoreBookingRequest $request
   *
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
      $numberOfInstallments = $validated['cart']['number_of_installments'];
      $isSigle = $validated['cart']['cabin_type'] === 'private-cabin' ? true : false;

      // Process booking data
      $bookingData = [
        'event_id' => $eventId,
        'customer_id' => $user->id,
        'payment_plan' => $paymentPlan,
        'number_of_installments' => $numberOfInstallments,
        'completed' => false,
        'is_cancelled' => false,
        'is_single_occupancy' => $isSigle,
        'tags' => json_encode(['New']),
      ];

      // get price from session
      // $price = $validated["cart"]["price_total"];
      $adjustments = Adjustment::where('event_id', $eventId)->first();
      $priceCalc = PriceCalculation::calculatePricePerPassenger([
        'cabinPrice' => $validated['cart']['cabin_price'],
        'cabinCapacity' => $validated['cart']['cabin_capacity'],
        'cabinType' => $cart['cabin_type'] === 'private-cabin' ? true : false,
        'selectedAdjustments' => $cart['addons'],
        'adjustments' => $adjustments,
      ]);

      $totalPassenger = $priceCalc['totalPassenger'];

      // Process passenger data
      $passengerData = [
        'confirmed_booking_email' => false,
        'lead_passenger' => $validated['cart']['cabin_type'] === 'private-cabin',
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
        'special_request' => $validated['specialRequest'] ?? null,
        'newsletter' => $validated['newsletter'],
        'travel_info' => false,
        'terms_n_cons' => $validated['terms'],
        'cabin_conf_accp' => $validated['cart']['cabin_conf_accp'],
        'single_t_agreement' => $validated['cart']['single_t_agreement'],
        // passenger allocated cost - take from calculation
        'passenger_allocated_cost' => $totalPassenger,
        'addons' => $validated['cart']['addons'],
        'passenger_balance' => 0,
        'was_on_board' => false,
      ];

      //call to booking repository method
      $result = $this->bookingRepository->createBooking($bookingData, $passengerData, null, $reservationId);

      // delete current sesion
      $request->session()->forget('cart');
      $request->session()->forget('reservation_id');

      return response()->json(
        [
          'message' => 'Booking created successfully.',
          'booking' => [
            'booking_code' => $result['booking']['booking_code'],
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

  /**
   * Get booking by booking code
   *
   * @param string $bookingCode
   * @return \Illuminate\Http\JsonResponse
   *
   */
  public function singleBooking($bookingCode)
  {
    $user = Auth::user();

    if (!$user) {
      return response()->json(['message' => 'Unauthorized'], 403);
    }

    $result = $this->customerBookingRepository->getBookingByCode($bookingCode, $user);
    $available_seats = $this->customerBookingService->getAvailableSeats($bookingCode);

    if (!$result) {
      return response()->json(['message' => 'Booking not found'], 404);
    }

    if ($result->status === 'NEW' || $result->status === 'CANCELLED') {
      $result->cabin->makeHidden(['cabin_number']);
      $result->cabin->cabinSpec->makeHidden(['cabin_number']);
    }

    $schema = [
      'booking' => $result,
      'available_seats' => $available_seats,
    ];

    return response()->json($schema);
  }

  /**
   * Get all bookings for the authenticated user
   *
   * @return \Illuminate\Http\JsonResponse
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
  public function emptySeat($bookingCode)
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

    $result = $this->customerBookingService->setEmptySeat($bookingCode);

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

    // create passenger with booking id
    $result = $this->customerBookingService->addPassengerManually($bookingCode, $validated);

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

    // get email and survivor number from request
    $email = $request->input('email');

    if (!$email) {
      return response()->json(['message' => 'Email and survivor number are required'], 400);
    }

    // if booking have passenger with this email
    $passenger = $booking->passengers()->where('email', $email)->first();

    if ($passenger) {
      return response()->json(['message' => 'Passenger already exists'], 400);
    }

    $result = $this->customerBookingService->addPassengerViaEmail($bookingCode, $email);

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
    $bookingCode = $request->input('bookingCode');

    // Validate the signed URL
    if (!$request->hasValidSignature(false)) {
      return response()->json(['message' => 'Invalid or expired URL'], 403);
    }

    // Fetch the booking and check if it exists
    $booking = Booking::where('booking_code', $bookingCode)->first();

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
  public function submitAddPassenger(StorePassengerRequest $request, $bookingCode)
  {
    $validated = $request->validated();
    // create passenger with booking id
    $result = $this->customerBookingService->addPassengerManually($bookingCode, $validated);

    return $result;
  }
}
