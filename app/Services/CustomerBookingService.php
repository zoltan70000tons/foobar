<?php

namespace App\Services;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;
use App\Repositories\CustomerBookingRepository;
use Illuminate\Support\Facades\Mail;
use App\Mail\AddPassenger;

class CustomerBookingService
{
  protected $customerBookingRepository;

  public function __construct(CustomerBookingRepository $customerBookingRepository)
  {
    $this->customerBookingRepository = $customerBookingRepository;
  }

  /*
  |--------------------------------------------------------------------------
  | Get my bookings
  |--------------------------------------------------------------------------
  |
  |  In this method we check the type of booking and status of the booking
  |
  */
  public function getMyBookings($user)
  {
    $bookings = $this->customerBookingRepository->getAllBookings($user);

    // if booking have status new then do not return cabin id and number
    $bookings->map(function ($booking) {
      if ($booking->status === "NEW") {
        $booking->cabin["cabin_id"] = null;
        $booking->cabin["cabin_number"] = null;
      }
    });

    return $bookings;
  }

  /*
  |--------------------------------------------------------------------------
  | Get available seats
  |--------------------------------------------------------------------------
  |
  |  This method will return the number of available seats in the cabin
  |
  */
  public function getAvailableSeats($bookingCode)
  {
    $booking = $this->customerBookingRepository->getBookingByCode($bookingCode);

    $cabin = $booking->cabin->cabinCategory->capacity;
    $passengers = $booking->passengers;

    $countOfAvaialble = $cabin - count($passengers);

    return $countOfAvaialble;
  }

  /*
  |--------------------------------------------------------------------------
  | Set empty seat
  |--------------------------------------------------------------------------
  |
  | This method will add an empty seat to the booking
  |
  */
  public function setEmptySeat($bookingCode)
  {
    $booking = $this->customerBookingRepository->getBookingByCode($bookingCode);

    $cabinCapacity = $booking->cabin->cabinCategory->capacity;
    $passengers = $booking->passengers;

    if (count($passengers) < $cabinCapacity) {
      // add one null passenger to the same booking
      try {
        $this->customerBookingRepository->createPassenger([
          "booking_id" => $booking->id,
          "empty_seat" => true,
          "passenger_allocated_cost" => 0,
          "passenger_balance" => 0,
        ]);
      } catch (\Exception $e) {
        return response()->json(["message" => "Error while adding empty seat"], 500);
      }

      return response()->json(["message" => "Empty seat added"], 200);
    }

    return response()->json(["message" => "No empty seats available"], 404);
  }

  /*
  |--------------------------------------------------------------------------
  | Add passenger manually
  |--------------------------------------------------------------------------
  |
  | This method will add a passenger to the booking manually
  |
  */
  public function addPassengerManually($bookingCode, $validated)
  {
    $booking = $this->customerBookingRepository->getBookingByCode($bookingCode);

    $cabinCapacity = $booking->cabin->cabinCategory->capacity;
    $passengers = $booking->passengers;

    if (count($passengers) < $cabinCapacity) {
      try {
        $this->customerBookingRepository->createPassenger([
          "booking_id" => $booking->id,
          "first_name" => $validated["firstName"],
          "middle_name" => $validated["middleName"],
          "last_name" => $validated["lastName"],
          "dob" => $validated["dateOfBirth"],
          "citizenship" => $validated["citizenship"],
          "address_first" => $validated["addressLine1"],
          "address_second" => $validated["addressLine2"],
          "city" => $validated["city"],
          "state" => $validated["state"],
          "postal_code" => $validated["zipCode"],
          "country" => $validated["country"],
          "email" => $validated["email"],
          "phone" => $validated["phoneNumber"],
          "emergency_c_name" => $validated["emergencyContactName"],
          "emergency_c_phone" => $validated["emergencyPhoneNumber"],
          "special_request" => $validated["specialRequest"],
          "passenger_allocated_cost" => 0.0,
          "passenger_balance" => 0.0,
        ]);
      } catch (\Exception $e) {
        \Log::error("Error while adding passenger: " . $e->getMessage());
        return response()->json(["message" => "Error while adding passenger"], 500);
      }

      return response()->json(["message" => "Passenger added"], 200);
    }

    return response()->json(["message" => "No empty seats available"], 404);
  }

  /*
  |--------------------------------------------------------------------------
  | Add passenger via email
  |--------------------------------------------------------------------------
  |
  | This method will send an email to the user with a signed URL to add a passenger
  |
  */
  public function addPassengerViaEmail($bookingCode, $email)
  {
    // generate signed url
    $getSignedURL = URL::temporarySignedRoute(
      "add.pax",
      Carbon::now()->addHours(24),
      ["bookingCode" => $bookingCode],
      false // Generate relative URL
    );

    // send email to the user
    try {
      $email = $email;
      Mail::to($email)->send(new AddPassenger($getSignedURL, $bookingCode));
    } catch (\Exception $e) {
      \Log::error("Failed to send email to user: " . $e->getMessage());
      return response()->json(["message" => "Failed to send email to user"], 500);
    }
  }
}
