<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Event;
use Illuminate\Support\Facades\App;
use App\Models\Cabin;
use App\Models\Booking;
use App\Models\TemporaryReservation;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\StoreBookingRequest;
use Illuminate\Support\Facades\DB;
use App\Models\Passenger;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
  /**
   * Show only events that are in pre-sale or public status
   *
   * If no events are found, return a message
   * Pre-sale: Only logged-in users whose membership status matches one of the entries in the new table above can book. The current date must also be within the range.
   * Public: Everyone can book.
   * Closed: Event is shown to the public, but no longer taking booking requests
   * Draft: The event exists only for admin users internally.
   *
   */
  public function show()
  {
    // check if event exist and get only if pre-sale or public
    $events = Event::where("status", "pre-sale")->orWhere("status", "public")->get();

    if ($events->isEmpty()) {
      return response()->json(["message" => "no events found"]);
    }

    // return events if exist
    return response()->json([
      "events" => $events,
    ]);
  }

  /**
   * Single event
   *
   *
   */
  public function showOne(Request $request, $id, $language = "en")
  {
    App::setLocale($language);

    // Retrieve the event
    $event = Event::find($id);

    if (!$event) {
      return response()->json([
        "status" => 404,
        "message" => __("event.no_event_found"),
      ]);
    }

    // Check event status
    if (!in_array($event->status, ["pre-sale", "public"])) {
      return response()->json([
        "status" => 403,
        "message" => __("event.no_event_found"),
      ]);
    }

    // Get purchase access information from the request
    $purchaseAccess = $request->get("purchase_access", false);
    $accessMessage = $request->get("access_message", "");

    return response()->json([
      "status" => 200,
      "event_status" => $event->status,
      "event" => $event,
      "purchase_access" => $purchaseAccess,
      "access_message" => $accessMessage,
    ]);
  }

  /**
   * Store a new booking
   *
   *
   */
  public function store(StoreBookingRequest $request)
  {
    Log::info("BookingController@store: " . json_encode($request->all()));

    $validated = $request->validated();

    // Get authenticated user
    $user = Auth::user();

    if (!$user) {
      return response()->json(["message" => "Unauthorized"], 403);
    }

    DB::beginTransaction();

    try {
      // Fetch temporary reservation data
      $tempReservation = TemporaryReservation::find($validated["cart"]["reservation_id"]);
      if (!$tempReservation) {
        throw new \Exception("Temporary reservation not found.");
      }

      // Fetch cabin data from the temporary reservation
      $cabinId = $tempReservation->cabin_id;
      $cabinNumber = $tempReservation->cabin_number;

      // Fetch cabin data
      $cabin = Cabin::find($cabinId);
      if (!$cabin) {
        throw new \Exception("Cabin not found for the given reservation ID.");
      }

      $cabinCategory = $cabin->category->id;

      // Validate cabin ID
      $cabinId = Cabin::where("id", $cabinId)->firstOrFail()->id;

      $characters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
      $identifier_code = substr(str_shuffle($characters), 0, 4);
      $bookingCode = "{$cabinNumber}-{$identifier_code}-{$cabinCategory}";

      $eventId = (int) $validated["cart"]["event_id"];
      $paymentPlan = $validated["cart"]["payment_plan"];
      $isSigle = $validated["cart"]["cabin_type"] === "private-cabin" ? true : false;

      // Process booking data
      $bookingData = [
        "booking_code" => $bookingCode,
        "event_id" => $eventId,
        "customer_id" => $user->id,
        "payment_plan" => $paymentPlan,
        "cabin_id" => $cabinId,
        "completed" => false,
        "is_cancelled" => false,
        "is_single_occupancy" => $isSigle,
        "tags" => json_encode(["New"]),
      ];

      Log::info("STOOORE@store: " . json_encode($bookingData));

      // Create booking
      $booking = Booking::create($bookingData);

      // Fetch user details
      $userDetails = $user->detail;

      if (!$userDetails) {
        throw new \Exception("User details not found.");
      }

      // Process passenger data
      $passengerData = [
        "booking_id" => $booking->id,
        "confirmed_booking_email" => false,
        "lead_passenger" => $validated["cart"]["cabin_type"] === "private-cabin",
        // Pull NON-EDITABLE data from the user
        "survivor_number" => $user->survivorNumber->survivor_number,
        "gender" => $userDetails->gender,
        "first_name" => $userDetails->first_name,
        "middle_name" => $userDetails->middle_name,
        "last_name" => $userDetails->last_name,
        "dob" => $userDetails->dob,
        "citizenship" => $userDetails->citizenship,
        // Pull EDITABLE data from the booking form
        "payment_method" => "CREDIT_CARD", // TODO: Implement payment method selection
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

      // Create passenger
      $passenger = Passenger::create($passengerData);

      // Commit transaction
      DB::commit();

      // Delete temporary reservation
      TemporaryReservation::find($validated["cart"]["reservation_id"])?->delete();
      // delete current sesion
      $request->session()->forget("cart");
      $request->session()->forget("reservation_id");

      return response()->json(
        [
          "message" => "Booking created successfully.",
          "booking" => $booking,
          "passenger" => $passenger,
        ],
        201
      );
    } catch (\Exception $e) {
      DB::rollBack();

      return response()->json(
        [
          "message" => "An error occurred while creating the booking.",
          "error" => $e->getMessage(),
        ],
        500
      );
    }
  }

  // My bookings
  public function myBookings()
  {
    $user = Auth::user();

    if (!$user) {
      return response()->json(["message" => "Unauthorized"], 403);
    }

    $bookings = Booking::where("customer_id", $user->id)->get();

    return response()->json(["bookings" => $bookings]);
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
