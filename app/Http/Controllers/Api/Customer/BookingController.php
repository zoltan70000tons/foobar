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
use App\Models\User;
use Illuminate\Validation\Rules\Numeric;

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
        'is_single_occupancy' => false,
        'tags' => json_encode(['New']),
      ];

      // get price from session
      // $price = $validated["cart"]["price_total"];
      $adjustments = Adjustment::where('event_id', $eventId)->first();
      $eventStatus = Event::find($eventId)->status;
      $priceCalc = PriceCalculation::calculatePricePerPassenger([
        'cabinPrice' => (float) $validated['cart']['cabin_price'],
        'cabinCapacity' => (int) $validated['cart']['cabin_capacity'],
        'cabinType' => $cart['cabin_type'] === 'private-cabin' ? true : false,
        'selectedAdjustments' => $cart['addons'],
        'adjustments' => $adjustments,
        'eventStatus' => $eventStatus,
      ]);

      $totalPassenger = $priceCalc['totalPassenger'];

      $language = $validated['language'] ?? 'en';

      // Process passenger data
      $passengerData = [
        'confirmed_booking_email' => false,
        'lead_passenger' => true,
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
      //      \Log::info('Booking created successfully', $result);

      // Delete current sesion
      $request->session()->forget('cart');
      $request->session()->forget('reservation_id');

      // \Log::info('Result ----> data: ', ['result' => $result]);
      // \Log::info('Passenger ----> data: ', ['passenger_data' => $passengerData]);
      // \Log::info('Cart ----> data: ', ['cart' => $cart]);

      $bookingCode = $result['booking']['booking_code'];
      $passengerEmail = $passengerData['email'];

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
      $this->sendConfirmationEmail($bookingCode, $passengerData, $cart, $language);

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
  private function sendConfirmationEmail($bookingCode, $passengerData, $cart, $language): void
  {
    try {
      // Get booking data with relationships
      $booking = Booking::where('booking_code', $bookingCode)
        ->with(['cabin.category', 'passengers.installments', 'adjustments'])
        ->first();

      // Convert booking to array for logging
      \Log::info('EMAIL Booking data', ['booking' => $booking->toArray()]);

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
          $totalPrice = $cart['price_total'];
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

      Mail::to($passengerData['email'])->send(
        new CustomerConfirmationBooking($booking, $cart, $installments, $language)
      );
    } catch (\Exception $e) {
      \Log::error('Failed to send booking confirmation email: ' . $e->getMessage());
    }
  }

  /*
  |--------------------------------------------------------------------------
  |  Get booking by code
  |--------------------------------------------------------------------------
  |
  |  This method return single booking by code related to the user
  |
  */
  public function singleBooking(int $eventId, string $bookingCode)
  {
    $user = Auth::user();

    $result = $this->customerBookingRepository->getBookingByCode($eventId, $bookingCode, $user);

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

    $invitations = PassengerInvitation::with(
      'booking',
      'booking.event',
      'booking.cabin.category',
      'booking.cabin.cabinType'
    )
      ->where('email', $user->email)
      ->get();

    // unset agent_id
    if ($invitations) {
      $invitations->map(function ($invitation) {
        // Remove agent_id from the booking.
        unset($invitation->booking->agent_id);

        // Get the lead passenger from the passengers table where booking_id matches and lead_passenger is true.
        $leadPassenger = Passenger::where('booking_id', $invitation->booking->id)
          ->where('lead_passenger', true)
          ->first();

        // Attach the lead passenger data to the invitation.
        $invitation->invited_by = $leadPassenger->email;

        return $invitation;
      });
    }

    $result = $this->customerBookingService->getMyBookings($user);

    return response()->json([
      'bookings' => $result,
      'invitations' => $invitations ?? [],
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
  public function emptySeat(Request $request, int $eventId, string $bookingCode)
  {
    $user = Auth::user();

    $booking = Booking::where('booking_code', $bookingCode)->where('event_id', $eventId)->first();

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

    $result = $this->customerBookingRepository->setEmptySeat($eventId, $bookingCode, $passengerOrder);

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
  public function addPassenger(StorePassengerRequest $request, int $eventId, string $bookingCode)
  {
    $booking = Booking::where('booking_code', $bookingCode)->where('event_id', $eventId)->first();

    if (!$booking) {
      return response()->json(['message' => 'Booking not found'], 404);
    }

    if ($booking->is_single_occupancy) {
      return response()->json(['message' => 'This booking is single occupancy'], 400);
    }

    $validated = $request->validated();

    $passengerOrder = $request->input('passenger_order');
    $survivorNumber = $request->input('survivor_number') ?? null;

    if ($survivorNumber) {
      // check this suvivor number is not used in passengers
      $passenger = $booking->passengers()->where('survivor_number', $survivorNumber)->first();

      if ($passenger) {
        return response()->json(['message' => 'This user already is assigned to other booking'], 400);
      }
    }

    // Check if passenger order is already taken
    $passenger = $booking->passengers()->where('passenger_order', $passengerOrder)->first();
    if ($passenger->email) {
      return response()->json(['message' => 'Passenger order is already taken'], 400);
    }

    // create passenger with booking id
    $result = $this->customerBookingRepository->addPassengerManually($eventId, $bookingCode, $passenger, $validated);

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
  public function addPassengerViaEmail(Request $request, int $eventId, string $bookingCode)
  {
    $user = Auth::user();

    $email = $request->input('email');
    if (!$email) {
      return response()->json(['message' => 'Email is required'], 400);
    }

    if ($email === $user->email) {
      return response()->json(['message' => 'You cannot send an email invitation to yourself. Use manual add'], 400);
    }

    $booking = Booking::where('booking_code', $bookingCode)->where('event_id', $eventId)->first();

    if (!$booking) {
      return response()->json(['message' => 'Booking not found'], 404);
    }

    if ($booking->is_single_occupancy) {
      return response()->json(['message' => 'This booking is single occupancy'], 400);
    }

    $passengerOrder = $request->input('passenger_order');

    // Check if a passenger with this email is already added to the booking
    if ($booking->passengers()->where('email', $email)->exists()) {
      return response()->json(['message' => 'A passenger with this email is already added to the booking'], 400);
    }

    // Check if the passenger order is already taken
    $passenger = $booking->passengers()->where('passenger_order', $passengerOrder)->first();
    if ($passenger && $passenger->email) {
      return response()->json(['message' => 'Passenger order is already taken'], 400);
    }

    // Check if an invitation already exists for this email and booking
    if (
      PassengerInvitation::where('booking_id', $booking->id)
        ->where('email', $email)
        ->exists()
    ) {
      return response()->json(['message' => 'Passenger with this email has already been invited'], 400);
    }

    // Check if this slot is already taken
    if (
      PassengerInvitation::where('booking_id', $booking->id)
        ->where('passenger_id', $passenger->id)
        ->exists()
    ) {
      return response()->json(['message' => 'Passenger slot is already taken'], 400);
    }

    $customer = User::where('email', $email)->first();

    $invitation = $this->customerBookingRepository->createPassengerInvitation($passenger, $booking, $email);

    if ($customer) {
      $this->customerBookingService->addPassengerViaEmailDirectly($booking, $email);
    } else {
      $this->customerBookingService->addPassengerViaEmail($booking, $invitation, $email);
    }

    return response()->json(['message' => 'Invitation sent'], 200);
  }

  /*
  |--------------------------------------------------------------------------
  | Cancel invitation
  |--------------------------------------------------------------------------
  |
  |  Delete the invitation row
  |
  */
  public function cancelInvitation(Request $request, int $eventId, string $bookingCode)
  {
    $booking = Booking::where('booking_code', $bookingCode)->where('event_id', $eventId)->first();

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
