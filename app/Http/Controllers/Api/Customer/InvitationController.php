<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePassengerRequest;

use App\Repositories\BookingRepository;
use App\Repositories\CustomerBookingRepository;
use App\Services\CustomerBookingService;
use App\Models\PassengerInvitation;
use Illuminate\Support\Facades\Auth;

class InvitationController extends Controller {
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
  |  Show booking by booking code
  |--------------------------------------------------------------------------
  |
  |  Return booking related to passenger invitation token
  |
  */
    public function index(int $eventId, string $bookingCode, string $token) {
        $user = Auth::user();

        if (!$bookingCode && !$token) {
            return response()->json(
                [
                    'message' => 'Booking code or token is required',
                ],
                400,
            );
        }

        $passengerInvitation = PassengerInvitation::with(
            'booking',
            'booking.event',
            'booking.cabin.category',
            'booking.cabin.cabinType',
        )
            ->where('token', $token)
            ->first();

        // Check if the passenger invitation exists and is associated with a booking
        if (!$passengerInvitation || !$passengerInvitation->booking) {
            return response()->json(
                [
                    'message' => 'Invalid token or user data',
                ],
                400,
            );
        }

        // check the eventId is the same as the booking event id
        if ($passengerInvitation->booking->event_id !== $eventId) {
            return response()->json(
                [
                    'message' => 'Invalid token',
                ],
                400,
            );
        }

        if (!$passengerInvitation || $passengerInvitation->email !== $user->email) {
            return response()->json(
                [
                    'message' => 'Invalid token',
                ],
                400,
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
    public function addPax(StorePassengerRequest $request, int $eventId, string $bookingCode, string $token) {
        $user = Auth::user();

        $validated = $request->validated();

        $dataToUpdate = [
            'survivor_number' => $user->survivorNumber->survivor_number,
            'first_name' => $user->detail->first_name,
            'middle_name' => $user->detail->middle_name ?? null,
            'last_name' => $user->detail->last_name,
            'dob' => $user->detail->dob,
            'gender' => $user->detail->gender,
            'citizenship' => $user->detail->citizenship,
            'address_first' => $validated['address_line_1'],
            'address_second' => $validated['address_line_2'],
            'city' => $validated['city'],
            'state' => $validated['state'],
            'postal_code' => $validated['zip_code'],
            'country' => $validated['country'],
            'email' => $validated['email'],
            'phone' => $validated['phone_number'],
            'emergency_c_name' => $validated['emergency_contact_name'],
            'emergency_c_phone' => $validated['emergency_phone_number'],
            'special_request' => $validated['special_request'],
        ];

        // create passenger with booking id
        $result = $this->customerBookingRepository->addPassengerWithToken(
            $eventId,
            $bookingCode,
            $token,
            $dataToUpdate,
        );

        return $result;
    }

    /*
  |--------------------------------------------------------------------------
  |  Remove invitation
  |--------------------------------------------------------------------------
  |
  |  Remove invitation via invitation token
  |
  */
    public function removeInvitation(int $eventId, string $bookingCode, string $token) {
        $user = Auth::user();

        if (!$bookingCode && !$token && !$eventId) {
            return response()->json(
                [
                    'message' => 'Booking code or token is required',
                ],
                400,
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
                400,
            );
        }

        $passengerInvitation->delete();

        return response()->json([
            'message' => 'Invitation removed',
        ]);
    }
}
