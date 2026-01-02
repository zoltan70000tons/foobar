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
use App\Exceptions\ApiException;
use App\Helpers\ErrorResponse;
use App\Http\Resources\BookingResource;
use App\Http\Resources\BookingInvitationResource;

class BookingController extends Controller {
    use StringNormalization;

    protected $bookingRepository;
    protected $customerBookingService;
    protected $customerBookingRepository;

    public function __construct(
        BookingRepository $bookingRepository,
        CustomerBookingService $CustomerBookingService,
        CustomerBookingRepository $customerBookingRepository,
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
  |  @param  \App\Http\Requests\StoreBookingRequest  $request
  |  @param  \App\Services\UserInfoService  $userInfoService
  |  @return \Illuminate\Http\Response
  |
  */
    public function store(StoreBookingRequest $request, UserInfoService $userInfoService) {
        $validated = $request->validated();

        $user = Auth::user();
        $cart = $user ? Cart::where('user_id', $user->id)->first()?->cart_data ?? [] : [];

        if (!$cart || empty($cart)) {
            return ErrorResponse::error(
                'Your cart is empty or not found. Please add items to your cart.',
                ErrorCode::CART_EMPTY,
                400,
            );
        }

        // Age verification
        $dateOfBirth = $user->detail->dob ?? null;
        if ($dateOfBirth) {
            // If age restricion is false return error
            $isAgeValid = AgeRestriction::isAgeValid($dateOfBirth, 21);
            if (!$isAgeValid) {
                return ErrorResponse::error(__('feedback.age_restriction'), ErrorCode::AGE_RESTRICTION, 400);
            }
        } else {
            return ErrorResponse::error('Date of birth is required', ErrorCode::DOB_REQUIRED, 400);
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
                'addons' => $validated['cart']['addons'],
                'cart_snapshot' => $cart,
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
                'passenger_allocated_cost' => $totalPassenger,
                'passenger_balance' => 0,
                'was_on_board' => false,
                'language' => $validated['language'] ?? 'en',
            ];

            // Call to booking repository method
            $result = $this->bookingRepository->createBooking($bookingData, $passengerData, null, $reservationId);

            // Check if repository returned an error
            if (isset($result['error']) && $result['error'] === true) {
                return ErrorResponse::error($result['message'], ErrorCode::UNKNOWN_ERROR, 500);
            }

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
                    201,
                );
            }

            // Update user profile if requested
            if ($validated['update_contact_info'] === true) {
                $result = $userInfoService->updateUserProfile($user, $validated);
            }

            try {
                $bookingObj = Booking::where('booking_code', $bookingCode)->first();

                Notification::route('slack', env('SLACK_BOOKING_ENGINE_NOTIFICATIONS'))->notify(
                    new NewBookingRequest(
                        $bookingRequestId,
                        $user->survivorNumber->survivor_number,
                        $passengerEmail,
                        $cart['cabin_type'],
                        $bookingObj,
                    ),
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
                ],
                201,
            );
        } catch (ApiException $e) {
            return ErrorResponse::error($e->getMessage(), $e->getErrorCode(), $e->getHttpStatusCode());
        } catch (\Exception $e) {
            // Log unexpected errors
            Log::error('Unexpected error in booking creation: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);
            return ErrorResponse::error('Unexpected error in booking creation', ErrorCode::UNKNOWN_ERROR, 500);
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
    private function sendConfirmationEmail($bookingCode, $passengerData, $cart, $language, $event): void {
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
                new CustomerConfirmationBooking($booking, $cart, $installments, $language, $event),
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
    public function singleBooking(int $eventId, string $bookingCode) {
        $user = Auth::user();

        // if event status is not public or pre-sale return error
        $event = Event::find($eventId);

        if (!$event || !in_array($event->status, ['PUBLIC', 'PRE-SALE'])) {
            return ErrorResponse::error('Booking not found', ErrorCode::BOOKING_NOT_FOUND, 404);
        }

        $booking = $this->customerBookingRepository->getBookingByCode($eventId, $bookingCode, $user);

        if (!$booking) {
            return ErrorResponse::error('Booking not found', ErrorCode::BOOKING_NOT_FOUND, 404);
        }

        // return response()->json($schema);
        return response()->json([
            'booking' => new BookingResource($booking),
        ]);
    }

    /*
  |--------------------------------------------------------------------------
  |  Get booking by request id
  |--------------------------------------------------------------------------
  |
  |  This method return single booking by request id
  |
  */
    public function singleBookingByRequestId(int $eventId, string $requestId) {
        $user = Auth::user();

        // if event status is not public or pre-sale return error
        $event = Event::find($eventId);

        if (!$event || !in_array($event->status, ['PUBLIC', 'PRE-SALE'])) {
            return ErrorResponse::error('Booking not found', ErrorCode::BOOKING_NOT_FOUND, 404);
        }

        $result = $this->customerBookingRepository->getBookingByRequestId($eventId, $requestId, $user);

        if (!$result) {
            return ErrorResponse::error('Booking not found', ErrorCode::BOOKING_NOT_FOUND, 404);
        }

        return response()->json(
            [
                'booking' => new BookingResource($result),
            ],
            200,
        );
    }

    /*
  |--------------------------------------------------------------------------
  |  Get all bokings
  |--------------------------------------------------------------------------
  |
  |  This method return all bokings related to the user
  |
  */
    public function allBookings() {
        $user = Auth::user();

        $invitations = PassengerInvitation::with(
            'booking',
            'booking.event',
            'booking.cabin.category',
            'booking.cabin.cabinType',
            'booking.passengers',
        )
            ->where('email', $user->email)
            ->get();

        // $bookings = $this->customerBookingService->getMyBookings($user);
        $bookings = $this->customerBookingRepository->getAllBookings($user);

        return response()->json([
            'bookings' => BookingResource::collection($bookings),
            'invitations' => BookingInvitationResource::collection($invitations),
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
    public function emptySeat(Request $request, int $eventId, string $bookingCode) {
        $language = $request->input('language', 'en');
        App::setLocale($language);

        $user = Auth::user();

        $booking = Booking::where('booking_code', $bookingCode)->where('event_id', $eventId)->first();

        // no booking
        if (!$booking) {
            return ErrorResponse::error(__('feedback.booking_not_found'), ErrorCode::BOOKING_NOT_FOUND, 404);
        }

        if ($booking->is_single_occupancy) {
            return ErrorResponse::error(
                __('bookings.error.single_occupancy'),
                ErrorCode::SINGLE_OCCUPANCY_BOOKING,
                400,
            );
        }

        // not the owner
        if ($booking->customer_id !== $user->id) {
            return ErrorResponse::error('Unauthorized', ErrorCode::UNAUTHORIZED, 403);
        }

        $passengerOrder = $request->input('passenger_order');

        $result = $this->customerBookingRepository->setEmptySeat($eventId, $bookingCode, $passengerOrder);

        GlobalLogger::log(LogActionBooking::USER_SET_EMPTY_SEAT, 'booking', $booking->id, 'BY USER: Set empty seat', [
            'additional' => [
                'passengerOrder' => $passengerOrder,
            ],
        ]);

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
    public function removeEmptySeat(Request $request, int $eventId, string $bookingCode) {
        $language = $request->input('language', 'en');
        App::setLocale($language);

        $user = Auth::user();

        $booking = Booking::where('booking_code', $bookingCode)->where('event_id', $eventId)->first();

        // no booking
        if (!$booking) {
            return ErrorResponse::error(__('feedback.booking_not_found'), ErrorCode::BOOKING_NOT_FOUND, 404);
        }

        if ($booking->is_single_occupancy) {
            return ErrorResponse::error(
                __('bookings.error.single_occupancy'),
                ErrorCode::SINGLE_OCCUPANCY_BOOKING,
                400,
            );
        }

        // not the owner
        if ($booking->customer_id !== $user->id) {
            return ErrorResponse::error('Unauthorized', ErrorCode::UNAUTHORIZED, 403);
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
    public function addPassenger(StorePassengerRequest $request, int $eventId, string $bookingCode) {
        $booking = Booking::where('booking_code', $bookingCode)->where('event_id', $eventId)->first();

        if (!$booking) {
            return ErrorResponse::error(__('feedback.booking_not_found'), ErrorCode::BOOKING_NOT_FOUND, 404);
        }

        if ($booking->is_single_occupancy) {
            return ErrorResponse::error(
                __('bookings.error.single_occupancy'),
                ErrorCode::SINGLE_OCCUPANCY_BOOKING,
                400,
            );
        }

        $validated = $request->validated();

        $passengerOrder = $request->input('passenger_order');
        $survivorNumber = $request->input('survivor_number') ?? null;

        if ($survivorNumber) {
            $survivorNumberExist = SurvivorNumber::where('survivor_number', $survivorNumber)->exists();

            if (!$survivorNumberExist) {
                return ErrorResponse::error(
                    __('feedback.survivor_number_not_found'),
                    ErrorCode::SURVIVOR_NUMBER_NOT_FOUND,
                    404,
                );
            }

            $userWithSurvivor = User::with(['survivorNumber', 'detail'])
                ->whereHas('survivorNumber', function ($query) use ($survivorNumber) {
                    $query->where('survivor_number', $survivorNumber);
                })
                ->first();

            // Normalize input names
            $formattedName = $this->normalizeString($request->first_name);
            $formattedLastName = $this->normalizeString($request->last_name);

            // check the user details matched userWithSuvivor
            if (
                !$userWithSurvivor ||
                !$this->isSimilar(
                    $this->normalizeString($userWithSurvivor->detail->first_name ?? ''),
                    $formattedName,
                ) ||
                !$this->isSimilar(
                    $this->normalizeString($userWithSurvivor->detail->last_name ?? ''),
                    $formattedLastName,
                )
            ) {
                return ErrorResponse::error(
                    __('feedback.survivor_number_not_match'),
                    ErrorCode::SURVIVOR_NUMBER_NOT_FOUND,
                    404,
                );
            }

            // check this suvivor number is not used in passengers
            $passenger = $booking->passengers()->where('survivor_number', $survivorNumber)->first();

            if ($passenger) {
                return ErrorResponse::error(
                    __('bookings.error.email_already_used'),
                    ErrorCode::EMAIL_ALREADY_USED_IN_ANOTHER_BOOKING,
                    400,
                );
            }
        }

        // Check if passenger order is already taken
        $passenger = $booking->passengers()->where('passenger_order', $passengerOrder)->first();
        if ($passenger->email) {
            return ErrorResponse::error(
                __('bookings.error.passenger_order_taken'),
                ErrorCode::PASSENGER_ORDER_TAKEN,
                400,
            );
        }

        // create passenger with booking id
        $result = $this->customerBookingRepository->addPassengerManually(
            $eventId,
            $bookingCode,
            $passenger,
            $validated,
        );

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
    public function addPassengerViaEmail(Request $request, int $eventId, string $bookingCode) {
        // that can be used to send non-customer invitations with different language
        $invitationLanguage = $request->input('invitation_language', 'en');

        $user = Auth::user();

        $email = $request->input('email');

        // validate email
        if (!$email) {
            return ErrorResponse::error(__('bookings.error.email_required'), ErrorCode::UNAUTHORIZED, 400);
        }

        // cannot invite yourself
        if ($email === $user->email) {
            return ErrorResponse::error(
                __('bookings.error.cannot_invite_yourself'),
                ErrorCode::CANNOT_INVITE_YOURSELF,
                400,
            );
        }

        $booking = Booking::where('booking_code', $bookingCode)->where('event_id', $eventId)->first();

        // no booking
        if (!$booking) {
            return ErrorResponse::error(__('feedback.booking_not_found'), ErrorCode::BOOKING_NOT_FOUND, 404);
        }

        // single occupancy
        if ($booking->is_single_occupancy) {
            return ErrorResponse::error(
                __('bookings.error.single_occupancy'),
                ErrorCode::SINGLE_OCCUPANCY_BOOKING,
                400,
            );
        }

        $passengerOrder = $request->input('passenger_order');

        // Check if a passenger with this email is already added to the booking
        if ($booking->passengers()->where('email', $email)->exists()) {
            return ErrorResponse::error(
                __('bookings.error.email_already_used'),
                ErrorCode::EMAIL_ALREADY_USED_IN_ANOTHER_BOOKING,
                400,
            );
        }

        // Check if the passenger order is already taken
        $passenger = $booking->passengers()->where('passenger_order', $passengerOrder)->first();
        if ($passenger && $passenger->email) {
            return ErrorResponse::error(
                __('bookings.error.passenger_order_taken'),
                ErrorCode::PASSENGER_ORDER_TAKEN,
                400,
            );
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
            return ErrorResponse::error(
                __('bookings.error.email_already_used'),
                ErrorCode::EMAIL_ALREADY_USED_IN_ANOTHER_BOOKING,
                400,
            );
        }

        // Check if an invitation already exists for this email and booking
        if (
            PassengerInvitation::where('booking_id', $booking->id)
                ->where('email', $email)
                ->exists()
        ) {
            return ErrorResponse::error(
                __('bookings.error.email_already_invited'),
                ErrorCode::ALREADY_HAS_INVITATION,
                400,
            );
        }

        // Check if this slot is already taken
        if (
            PassengerInvitation::where('booking_id', $booking->id)
                ->where('passenger_id', $passenger->id)
                ->exists()
        ) {
            return ErrorResponse::error(__('bookings.error.slot_taken'), ErrorCode::PASSENGER_SLOT_TAKEN, 400);
        }

        $customer = User::where('email', $email)->first();

        $invitation = $this->customerBookingRepository->createPassengerInvitation($passenger, $booking, $email);

        $fromWho = $user->detail->first_name . ' ' . $user->detail->last_name;
        $toWho = $customer ? $customer->detail->first_name : $email;
        $toWhoLang = $customer && $customer->detail ? $customer->detail->language : $invitationLanguage;
        $toWhoLang = $toWhoLang ?: 'en';
        $event = Event::find($eventId);

        if ($customer) {
            $this->customerBookingService->addPassengerViaEmailDirectly(
                $booking,
                $email,
                $fromWho,
                $toWho,
                $event,
                $toWhoLang,
            );
        } else {
            $this->customerBookingService->addPassengerViaEmail(
                $booking,
                $invitation,
                $email,
                $fromWho,
                $toWho,
                $event,
                $toWhoLang,
            );
        }

        GlobalLogger::log(
            LogActionBooking::USER_ADD_PASSENGER_VIA_EMAIL,
            'booking',
            $booking->id,
            'BY USER: Add passenger via email',
            [
                'additional' => [
                    'toWho' => $toWho,
                    'isInvitedCustomer' => !!$customer,
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
    public function cancelInvitation(Request $request, int $eventId, string $bookingCode) {
        $language = $request->input('language', 'en');
        App::setLocale($language);

        $booking = Booking::where('booking_code', $bookingCode)->where('event_id', $eventId)->first();

        if (!$booking) {
            return ErrorResponse::error(__('feedback.booking_not_found'), ErrorCode::BOOKING_NOT_FOUND, 404);
        }

        $passengerId = $request->input('passenger_id');
        $invitationId = $request->input('passenger_invitation_id');
        $token = $request->input('passenger_invitation_token');

        if (!$passengerId || !$invitationId || !$token) {
            return ErrorResponse::error(
                'Passenger ID, Invitation ID, and Token are required',
                ErrorCode::UNKNOWN_ERROR,
                400,
            );
        }

        $passengerInvitation = PassengerInvitation::where('id', $invitationId)
            ->where('booking_id', $booking->id)
            ->where('passenger_id', $passengerId)
            ->where('token', $token)
            ->first();

        if (!$passengerInvitation) {
            return ErrorResponse::error('Invitation not found', ErrorCode::INVITATION_NOT_FOUND, 404);
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
    public function cancelInvitationByOrder(Request $request, int $eventId, string $bookingCode) {
        try {
            $language = $request->input('language', 'en');
            App::setLocale($language);

            $booking = Booking::where('booking_code', $bookingCode)->where('event_id', $eventId)->first();

            if (!$booking) {
                return ErrorResponse::error(__('feedback.booking_not_found'), ErrorCode::BOOKING_NOT_FOUND, 404);
            }

            $passengerOrder = $request->input('passenger_order');

            if (!$passengerOrder) {
                return ErrorResponse::error('Error with payload', ErrorCode::UNKNOWN_ERROR, 400);
            }

            $passenger = Passenger::query()
                ->where('booking_id', $booking->id)
                ->where('passenger_order', $passengerOrder)
                ->first();

            if (!$passenger) {
                return ErrorResponse::error('Passenger not found', ErrorCode::PASSENGER_NOT_FOUND, 404);
            }

            $passengerInvitation = PassengerInvitation::query()
                ->where('booking_id', $booking->id)
                ->where('passenger_id', $passenger->id)
                ->first();

            if (!$passengerInvitation) {
                return ErrorResponse::error('Invitation not found', ErrorCode::INVITATION_NOT_FOUND, 404);
            }

            $passengerInvitation->delete();

            return response()->json(['message' => 'Invitation cancelled'], 200);
        } catch (\Exception $e) {
            return ErrorResponse::error($e->getMessage(), ErrorCode::UNKNOWN_ERROR, 418);
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
    public function resetPassengerSeat(Request $request, int $eventId, string $bookingCode) {
        $language = $request->input('language', 'en');
        App::setLocale($language);

        $user = Auth::user();

        $booking = Booking::where('booking_code', $bookingCode)->where('event_id', $eventId)->first();

        if (!$booking) {
            return ErrorResponse::error(__('feedback.booking_not_found'), ErrorCode::BOOKING_NOT_FOUND, 404);
        }

        if ($booking->is_single_occupancy) {
            return ErrorResponse::error('This booking is single occupancy', ErrorCode::SINGLE_OCCUPANCY_BOOKING, 400);
        }

        // not the owner
        if ($booking->customer_id !== $user->id) {
            return ErrorResponse::error('Unauthorized', ErrorCode::UNAUTHORIZED, 403);
        }

        // if user is the lead passenger and request passenger id is assigned to his survivor number, throw error
        $passengerOrder = $request->input('passenger_order');
        $passModel = $booking->passengers()->where('passenger_order', $passengerOrder)->first();

        if (!$passModel) {
            return ErrorResponse::error('Passenger not found', ErrorCode::PASSENGER_NOT_FOUND, 404);
        }

        $passSurvivorNumber = $passModel->survivor_number;
        $userSurvivorNumber = $user->survivorNumber->survivor_number;

        if ($passSurvivorNumber === $userSurvivorNumber) {
            return ErrorResponse::error('You cannot reset your own seat', ErrorCode::CANNOT_RESET_OWN_SEAT, 400);
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
