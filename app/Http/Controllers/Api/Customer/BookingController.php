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

    \Log::info("VALIDATED@store: " . json_encode($validated));

    // Get authenticated user
    $user = Auth::user();

    if (!$user) {
      return response()->json(["message" => "Unauthorized"], 403);
    }

    try {
      $reservationId = $validated["cart"]["reservation_id"];
      $eventId = (int) $validated["cart"]["event_id"];
      $paymentPlan = $validated["cart"]["payment_plan"];
      $isSigle = $validated["cart"]["cabin_type"] === "private-cabin" ? true : false;

      // Process booking data
      $bookingData = [
        "event_id" => $eventId,
        "customer_id" => $user->id,
        "payment_plan" => $paymentPlan,
        "completed" => false,
        "is_cancelled" => false,
        "is_single_occupancy" => $isSigle,
        "tags" => json_encode(["New"]),
      ];

      // Process passenger data
      $passengerData = [
        "confirmed_booking_email" => false,
        "lead_passenger" => $validated["cart"]["cabin_type"] === "private-cabin",
        "payment_method" => "CREDIT_CARD",
        "address_first" => $validated["addressLine1"],
        "address_second" => $validated["addressLine2"],
        "city" => $validated["city"],
        "state" => $validated["state"],
        "postal_code" => $validated["zipCode"],
        "country" => $validated["country"],
        "email" => $validated["email"],
        "phone" => $validated["phone"]["number"],
        "emergency_c_name" => $validated["emergencyContactName"],
        "emergency_c_phone" => $validated["emergencyContactPhone"]["number"],
        "special_request" => $validated["specialRequest"] ?? null,
        "newsletter" => $validated["newsletter"],
        "travel_info" => false,
        "terms_n_cons" => $validated["terms"],
        "cabin_conf_accp" => false,
        "single_t_agreement" => false,
        "passenger_allocated_cost" => $validated["cart"]["price_total"],
        "passenger_balance" => 0,
        "was_on_board" => false,
      ];
      //call to booking repository method
      $result = $this->bookingRepository->createBooking($bookingData, $passengerData, null, $reservationId);

      \Log::info("RESULT@store: " . json_encode($result));

      // delete current sesion
      $request->session()->forget("cart");
      $request->session()->forget("reservation_id");

      return response()->json(
        [
          "message" => "Booking created successfully.",
          "booking" => [
            "booking_code" => $result["booking"]["booking_code"],
          ],
          // "passenger" => $result["passenger"],
        ],
        201
      );
    } catch (\Exception $e) {
      return response()->json(
        [
          "message" => "An error occurred while creating the booking.",
          "error" => $e->getMessage(),
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
  public function showBooking($bookingCode)
  {
    $result = $this->customerBookingRepository->getBookingByCode($bookingCode);
    $available_beds = $this->customerBookingService->getAvailableSeats($bookingCode);

    if (!$result) {
      return response()->json(["message" => "Booking not found"], 404);
    }

    // if status is "New" then do not return cabin id and number
    if ($result->status === "NEW") {
      $result->cabin["cabin_id"] = null;
      $result->cabin["cabin_number"] = null;
    }

    $schema = [
      "booking" => $result,
      "available_beds" => $available_beds,
    ];

    return response()->json($schema);
  }

  /**
   * Get all bookings for the authenticated user
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function myBookings()
  {
    $user = Auth::user();

    if (!$user) {
      return response()->json(["message" => "Unauthorized"], 403);
    }

    $bookings = Booking::with("event", "cabin.cabinCategory")
      ->where("customer_id", $user->id)
      ->get();

    // if booking have status new then do not return cabin id and number
    $bookings->map(function ($booking) {
      if ($booking->status === "NEW") {
        $booking->cabin["cabin_id"] = null;
        $booking->cabin["cabin_number"] = null;
      }
    });

    return response()->json(["bookings" => $bookings]);
  }

  // Set empty seat
  public function emptySeat($bookingCode)
  {
    $user = Auth::user();

    // no user
    if (!$user) {
      return response()->json(["message" => "Unauthorized"], 403);
    }

    $booking = Booking::where("booking_code", $bookingCode)->first();

    // no booking
    if (!$booking) {
      return response()->json(["message" => "Booking not found"], 404);
    }

    // not the owner
    if ($booking->customer_id !== $user->id) {
      return response()->json(["message" => "Unauthorized"], 403);
    }

    $result = $this->customerBookingService->setEmptySeat($bookingCode);

    return $result;
  }

  // Add passenger manually
  public function addPassenger(StorePassengerRequest $request, $bookingCode)
  {
    $user = Auth::user();

    if (!$user) {
      return response()->json(["message" => "Unauthorized"], 403);
    }

    $booking = Booking::where("booking_code", $bookingCode)->first();

    if (!$booking) {
      return response()->json(["message" => "Booking not found"], 404);
    }

    $validated = $request->validated();

    \Log::info("VALIDATED@addPassenger: " . json_encode($validated));

    // create passenger with booking id
    $result = $this->customerBookingService->addPassengerManually($bookingCode, $validated);

    \Log::info("RESULT@addPassenger: " . json_encode($result));

    return $result;
  }

  // Add passenger via email
  public function addPassengerViaEmail(Request $request, $bookingCode)
  {
    $user = Auth::user();

    if (!$user) {
      return response()->json(["message" => "Unauthorized"], 403);
    }

    $booking = Booking::where("booking_code", $bookingCode)->first();

    if (!$booking) {
      return response()->json(["message" => "Booking not found"], 404);
    }

    // get email and survivor number from request
    $email = $request->input("email");
    $survivorNumber = $request->input("survivor_number");

    if (!$email || !$survivorNumber) {
      return response()->json(["message" => "Email and survivor number are required"], 400);
    }

    $result = $this->customerBookingService->addPassengerViaEmail($bookingCode, $email, $survivorNumber);

    return $result;
  }

  // Delete booking
  public function destroy(Request $request, $id)
  {
    $user = Auth::user();

    if (!$user) {
      return response()->json(["message" => "Unauthorized"], 403);
    }

    $booking = Booking::find($id);

    if (!$booking) {
      return response()->json(["message" => "Booking not found"], 404);
    }

    if ($booking->customer_id !== $user->id) {
      return response()->json(["message" => "Unauthorized"], 403);
    }

    $booking->delete();

    return response()->json(["message" => "Booking deleted successfully"], 200);
  }
}
