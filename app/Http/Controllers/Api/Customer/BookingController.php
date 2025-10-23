<?php

namespace App\Http\Controllers\Api\Customer;

use App;
use App\Enums\GlobalLog\LogActionBooking;
use App\Http\Controllers\Controller;
use App\Support\GlobalLogger;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\StorePassengerRequest;
use Illuminate\Support\Facades\Auth;
use App\Repositories\BookingRepository;
use App\Repositories\CustomerBookingRepository;
use App\Services\CustomerBookingService;
use App\Services\UserInfoService;
use App\Models\Event;
use App\Models\PassengerInvitation;
use App\Mail\CustomerConfirmationBooking;
use App\Models\SurvivorNumber;
use App\Models\Passenger;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\Cart;
use App\Traits\StringNormalization;
use App\Helpers\AgeRestriction;
use App\Notifications\NewBookingRequest;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Enums\ErrorCode;

class BookingController extends Controller
{
  use StringNormalization;

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
  public function store(StoreBookingRequest $request, UserInfoService $userInfoService)
  {
    $validated = $request->validated();

    // Get authenticated user
    $user = Auth::user();
    $cart = $user ? Cart::where('user_id', $user->id)->first()?->cart_data ?? [] : [];

    if (!$cart || empty($cart)) {
      return response()->json([
        'errorLogId' => Str::uuid(),
        'errorMessage' => 'Cart is empty',
        'errorCode' => ErrorCode::CART_EMPTY->value,
      ], 400);
    }

    // Age verification
    $dateOfBirth = $user->detail->dob ?? null;
    if ($dateOfBirth) {
      // If age restricion is false return error
      $isAgeValid = AgeRestriction::isAgeValid($dateOfBirth, 21);
      if (!$isAgeValid) {
        return response()->json([
          'errorLogId' => Str::uuid(),
          'errorMessage' => __('feedback.age_restriction'),
          'errorCode' => ErrorCode::AGE_RESTRICTION->value,
        ], 400);
      }
    } else {
      return response()->json([
        'errorLogId' => Str::uuid(),
        'errorMessage' => 'Date of birth is required',
        'errorCode' => ErrorCode::DOB_REQUIRED->value,
      ], 400);
    }

    try {
      $reservationId = $validated['cart']['reservation_id'];
      $eventId = (int) $validated['cart']['event_id'];
      $paymentPlan = $validated['cart']['payment_plan'];

      //Determine the number of payment installments based on the selected payment plan.
      $numberOfInstallments = $paymentPlan === 'INSTALLMENTS' ? $validated['cart']['number_of_installments'] : 1;
      $bedConfig = $cart['cabin_type'] === 'private-cabin' ? $validated['bed_config'] : 'SEPARATED';

      // Process booking data
      $bookingData = [
        'event_id' => $eventId,
        'customer_id' => $user->id,
        'payment_plan' => $paymentPlan,
        'number_of_installments' => $numberOfInstallments ? $numberOfInstallments : 1,
        'is_single_occupancy' => false,
        'tags' => [],
        'bed_config' => $bedConfig,
      ];

      $event = Event::find($eventId);

      $totalPassenger = $cart['price_total_passenger'];

      $language = $validated['language'] ?? 'en';

      // Process passenger data
      $passengerData = [
        'survivor_number' => $user->survivorNumber->survivor_number,
        'confirmed_booking_email' => false,
        'lead_passenger' => true,
        'payment_method' => $validated['payment_method'],
        'address_first' => $validated['address_line_1'],
        'address_second' => $validated['address_line_2'],
        'city' => $validated['city'],
        'lang' => $language,
        'state' => $validated['state'],
        'postal_code' => $validated['zip_code'],
        'country' => $validated['country'],
        'email' => $validated['email'],
        'phone' => $validated['phone_number'],
        'emergency_c_name' => $validated['emergency_contact_name'],
        'emergency_c_phone' => $validated['emergency_phone_number'],
        'special_options' => $validated['special_options'],
        'special_request' => $validated['special_request'] ?? null,
        'dietary_preferences' => $validated['dietary_preferences'] ?? null,
        'newsletter' => $validated['newsletter'],
        'travel_info' => $validated['travel_info'],
        'hear_about' => $validated['info'],
        'referral_details' => $validated['referral_details'],
        'terms_n_cons' => $validated['terms'],
        'cabin_conf_accp' => $validated['cart']['cabin_conf_accp'],
        'single_t_agreement' => $validated['cart']['single_t_agreement'],
        // passenger allocated cost - take from calculation
        'passenger_allocated_cost' => $totalPassenger,
        'addons' => $validated['cart']['addons'],
        'passenger_balance' => 0,
        'was_on_board' => false,
        'language' => $validated['language'] ?? 'en',
      ];

      // Call to booking repository method
      $result = $this->bookingRepository->createBooking($bookingData, $passengerData, null, $reservationId);

      // Get cart variables for client response
      $totalPrice = $validated['cart']['price_total'];
      $cabinTitle = $validated['cart']['cabin_title'];

      // delete cart from db
      Cart::where('user_id', $user->id)->delete();
      
      // Show debug information only in local environment
      if (app()->environment('local')) {
        \Log::info('Result ----> data: ', ['result' => $result]);
        \Log::info('Passenger ----> data: ', ['passenger_data' => $passengerData]);
        \Log::info('Cart ----> data: ', ['cart' => $cart]);
      }

      $bookingCode = $result['booking']['booking_code'];
      $passengerEmail = $passengerData['email'];
      $postalCode = $passengerData['postal_code'];
      $bookingRequestId = $result['booking']['booking_request_id'];

      if (!$bookingCode || !$passengerEmail) {
        return response()->json(
          [
            'message' => 'Booking created successfully, but failed to send confirmation email.',
            'booking' => [
              'booking_request_id' => $bookingRequestId,
            ],
          ],
          201
        );
      }
      
      // Update user profile if requested
      if ($validated['update_contact_info'] === true) {
        $result =  $userInfoService->updateUserProfile($user, $validated);
      }

      try {
        $bookingObj = Booking::where('booking_code', $bookingCode)->first();

        Notification::route('slack', env('SLACK_BOOKING_ENGINE_NOTIFICATIONS'))->notify(
          new NewBookingRequest(
            $bookingRequestId,
            $user->survivorNumber->survivor_number,
            $passengerEmail,
            $cart['cabin_type'],
            $bookingObj
          )
        );
      } catch (\Exception $e) {
        // Optionally log the failure so you know something went wrong
        Log::warning('Slack notification failed: ' . $e->getMessage());
      }

      // Send confirmation email
      $this->sendConfirmationEmail($bookingCode, $passengerData, $cart, $language, $event);

      return response()->json(
        [
          'message' => 'Booking created successfully.',
          'user' => [
            'email' => $passengerEmail,
            'postal_code' => $postalCode,
          ],
          'booking' => [
            'booking_request_id' => $bookingRequestId,
            'total_price' => $totalPrice,
            'cabin_title' => $cabinTitle,
          ],
          // "passenger" => $result["passenger"],
        ],
        201
      );
    } catch (\Exception $e) {
        dd($e->getMessage());
      return response()->json(
        [
          'errorLogId' => Str::uuid(),
          'errorMessage' => $e->getMessage(),
          'errorCode' => ErrorCode::UNKNOWN_ERROR->value,
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
  private function sendConfirmationEmail($bookingCode, $passengerData, $cart, $language, $event): void
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

      Mail::to($passengerData['email'])->queue(
        new CustomerConfirmationBooking($booking, $cart, $installments, $language, $event)
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

    // if event status is not public or pre-sale return error
    $event = Event::find($eventId);

    if (!$event || !in_array($event->status, ['PUBLIC', 'PRE-SALE'])) {
      return response()->json(['message' => 'Booking not found'], 404);
    }

    $result = $this->customerBookingRepository->getBookingByCode($eventId, $bookingCode, $user);

    if (!$result) {
      return response()->json(['message' => 'Booking not found'], 404);
    }

    // hide cabin number if status is new or cancelled
    if ($result->status === 'NEW' || $result->status === 'CANCELLED') {
      $result->cabin->makeHidden(['cabin_number']);
      $result->cabin->makeHidden(['internal_notes']);
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
  |  Get booking by request id
  |--------------------------------------------------------------------------
  |
  |  This method return single booking by request id
  |
  */
  public function singleBookingByRequestId(int $eventId, string $requestId)
  {
    $user = Auth::user();

    // if event status is not public or pre-sale return error
    $event = Event::find($eventId);

    if (!$event || !in_array($event->status, ['PUBLIC', 'PRE-SALE'])) {
      return response()->json(['message' => 'Booking not found'], 404);
    }

    $result = $this->customerBookingRepository->getBookingByRequestId($eventId, $requestId, $user);

    if (!$result) {
      return response()->json(['message' => 'Booking not found'], 404);
    }

    // hide cabin number if status is new or cancelled
    if ($result->status === 'NEW' || $result->status === 'CANCELLED') {
      $result->cabin->makeHidden(['cabin_number']);
      $result->makeHidden(['booking_code', 'agent_id']);
      $result->cabin->makeHidden(['internal_notes']);
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
    $language = $request->input('language', 'en');
    App::setLocale($language);

    $user = Auth::user();

    $booking = Booking::where('booking_code', $bookingCode)->where('event_id', $eventId)->first();

    // no booking
    if (!$booking) {
      return response()->json(['message' => __('feedback.booking_not_found')], 404);
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

      GlobalLogger::log(
          LogActionBooking::USER_SET_EMPTY_SEAT,
          'booking',
          $booking->id,
          'BY USER: Set empty seat',
          [
              'additional' => [
                  'passengerOrder' => $passengerOrder,
              ],
          ],
      );

    return $result;
  }

  /*
  |--------------------------------------------------------------------------
  | Remove empty seat
  |--------------------------------------------------------------------------
  |
  |  This method call the service to unset an empty seat in the booking to aviailable
  |
  */
  public function removeEmptySeat(Request $request, int $eventId, string $bookingCode)
  {
    $language = $request->input('language', 'en');
    App::setLocale($language);

    $user = Auth::user();

    $booking = Booking::where('booking_code', $bookingCode)->where('event_id', $eventId)->first();

    // no booking
    if (!$booking) {
      return response()->json(['message' => __('feedback.booking_not_found')], 404);
    }

    if ($booking->is_single_occupancy) {
      return response()->json(['message' => 'This booking is single occupancy'], 400);
    }

    // not the owner
    if ($booking->customer_id !== $user->id) {
      return response()->json(['message' => 'Unauthorized'], 403);
    }

    $passengerOrder = $request->input('passenger_order');

    $result = $this->customerBookingRepository->removeEmptySeat($eventId, $bookingCode, $passengerOrder);

      GlobalLogger::log(
          LogActionBooking::USER_REMOVED_EMPTY_SEAT,
          'booking',
          $booking->id,
          'BY USER: Removed empty seat',
          [
              'additional' => [
                  'passengerOrder' => $passengerOrder,
              ],
          ],
      );

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
      return response()->json(['message' => __('feedback.booking_not_found')], 404);
    }

    if ($booking->is_single_occupancy) {
      return response()->json(['message' => 'This booking is single occupancy'], 400);
    }

    $validated = $request->validated();

    $passengerOrder = $request->input('passenger_order');
    $survivorNumber = $request->input('survivor_number') ?? null;

    if ($survivorNumber) {
      $survivorNumberExist = SurvivorNumber::where('survivor_number', $survivorNumber)->exists();

      if (!$survivorNumberExist) {
        return response()->json(['message' => 'Survivor number not found'], 404);
      }

      $userWithSurvivor = User::with(['survivorNumber', 'detail'])
        ->whereHas('survivorNumber', function ($query) use ($survivorNumber) {
          $query->where('survivor_number', $survivorNumber);
        })
        ->first();

      //$dateOfBirth = $request->dateOfBirth;
      // Normalize input names
      $formattedName = $this->normalizeString($request->first_name);
      $formattedLastName = $this->normalizeString($request->last_name);

      // check the user details matched userWithSuvivor
      if (
        !$userWithSurvivor ||
        !$this->isSimilar($this->normalizeString($userWithSurvivor->detail->first_name ?? ''), $formattedName) ||
        !$this->isSimilar($this->normalizeString($userWithSurvivor->detail->last_name ?? ''), $formattedLastName)
        // $userWithSurvivor->detail->dob !== $dateOfBirth
      ) {
        return response()->json(
          [
            'message' => __('feedback.survivor_number_not_match'),
          ],
          404
        );
      }

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

      GlobalLogger::log(
          LogActionBooking::USER_ADD_PASSENGER_MANUALLY,
          'booking',
          $booking->id,
          'BY USER: Add passenger manually',
          [
              'additional' => [
                  'email' => $validated['email'],
              ],
          ],
      );

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
    $language = $request->input('language', 'en');
    App::setLocale($language);

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
      return response()->json(['message' => __('feedback.booking_not_found')], 404);
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

    // if this email is used in another booking around the same event return error
    if (
      Booking::where('event_id', $eventId)
      ->where('status', '!=', 'CANCELLED')
      ->whereHas('passengers', function ($query) use ($email) {
        $query->where('email', $email);
      })
      ->exists()
    ) {
      return response()->json(
        ['message' => 'This email is already used in another booking, use manual add instead'],
        400
      );
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

    $fromWho = $user->detail->first_name . ' ' . $user->detail->last_name;
    $toWho = $customer ? $customer->detail->first_name : $email;
    $event = Event::find($eventId);

    if ($customer) {
      $this->customerBookingService->addPassengerViaEmailDirectly($booking, $email, $fromWho, $toWho, $event);
    } else {
      $this->customerBookingService->addPassengerViaEmail($booking, $invitation, $email, $fromWho, $toWho, $event);
    }

      GlobalLogger::log(
          LogActionBooking::USER_ADD_PASSENGER_VIA_EMAIL,
          'booking',
          $booking->id,
          'BY USER: Add passenger via email',
          [
              'additional' => [
                  'toWho' => $toWho,
                  'isInvitedCustomer'=> !!$customer,
              ],
          ],
      );

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
    $language = $request->input('language', 'en');
    App::setLocale($language);

    $booking = Booking::where('booking_code', $bookingCode)->where('event_id', $eventId)->first();

    if (!$booking) {
      return response()->json(['message' => __('feedback.booking_not_found')], 404);
    }

    // passenger_id: passengerId,
    // passenger_invitation_id: invitationId,
    // passenger_invitation_token: token,
    $passengerId = $request->input('passenger_id');
    $invitationId = $request->input('passenger_invitation_id');
    $token = $request->input('passenger_invitation_token');

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

      GlobalLogger::log(
          LogActionBooking::USER_CANCELLED_INVITATION,
          'booking',
          $booking->id,
          'BY USER: Cancelled invitation',
      );

    return response()->json(['message' => 'Invitation cancelled'], 200);
  }

    /*
    |--------------------------------------------------------------------------
    | Cancel invitation by passengerOrder
    |--------------------------------------------------------------------------
    |
    |  Delete the invitation row
    |
    */
    public function cancelInvitationByOrder(Request $request, int $eventId, string $bookingCode)
    {
        try {
            $language = $request->input('language', 'en');
            App::setLocale($language);

            $booking = Booking::where('booking_code', $bookingCode)->where('event_id', $eventId)->first();

            if (!$booking) {
                return response()->json(['message' => __('feedback.booking_not_found')], 404);
            }

            $passengerOrder = $request->input('passenger_order');

            if (!$passengerOrder) {
                return response()->json(['message' => 'Error with payload'], 400);
            }

            $passenger = Passenger::query()
                ->where('booking_id', $booking->id)
                ->where('passenger_order', $passengerOrder)
                ->first();

            if (!$passenger) {
                return response()->json(['message' => 'Passenger not found'], 404);
            }

            $passengerInvitation = PassengerInvitation::query()
                ->where('booking_id', $booking->id)
                ->where('passenger_id', $passenger->id)
                ->first();

            if (!$passengerInvitation) {
                return response()->json(['message' => 'Invitation not found'], 404);
            }

            $passengerInvitation->delete();

            return response()->json(['message' => 'Invitation cancelled'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 418);
        }
    }

  /*
  |--------------------------------------------------------------------------
  | Reset passenger seat
  |--------------------------------------------------------------------------
  |
  |  This method call the service to reset passenger seat
  |
  */
  public function resetPassengerSeat(Request $request, int $eventId, string $bookingCode)
  {
    $language = $request->input('language', 'en');
    App::setLocale($language);

    $user = Auth::user();

    $booking = Booking::where('booking_code', $bookingCode)->where('event_id', $eventId)->first();

    if (!$booking) {
      return response()->json(['message' => __('feedback.booking_not_found')], 404);
    }

    if ($booking->is_single_occupancy) {
      return response()->json(['message' => 'This booking is single occupancy'], 400);
    }

    // not the owner
    if ($booking->customer_id !== $user->id) {
      return response()->json(['message' => 'Unauthorized'], 403);
    }

    // if user is the lead passenger and request passenger id is assigned to his survivor number, throw error

    $passengerOrder = $request->input('passenger_order');
    $passModel = $booking->passengers()->where('passenger_order', $passengerOrder)->first();

    if (!$passModel) {
      return response()->json(['message' => 'Passenger not found'], 404);
    }

    $passSurvivorNumber = $passModel->survivor_number;
    $userSurvivorNumber = $user->survivorNumber->survivor_number;

    if ($passSurvivorNumber === $userSurvivorNumber) {
      return response()->json(['message' => 'You cannot reset your own seat'], 400);
    }

    $result = $this->customerBookingRepository->resetPassengerSeat($eventId, $bookingCode, $passengerOrder);

      GlobalLogger::log(
          LogActionBooking::USER_RESET_PASSENGER_SEAT,
          'booking',
          $booking->id,
          'BY USER: Reset passenger seat',
          [
              'additional' => [
                  'passengerOrder' => $passengerOrder,
              ],
          ],
      );

    return $result;
  }
}
