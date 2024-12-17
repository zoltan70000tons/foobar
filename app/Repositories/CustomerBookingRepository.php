<?php

namespace App\Repositories;

use App\Models\Booking;
use App\Models\Passenger;
use App\Models\Cabin;

class CustomerBookingRepository
{
  protected $booking;
  protected $passenger;
  protected $cabin;

  public function __construct(Booking $booking, Passenger $passenger, Cabin $cabin)
  {
    $this->booking = $booking;
    $this->passenger = $passenger;
    $this->cabin = $cabin;
  }

  /**
   * Get booking by booking code
   * - with passengers ( depends on the booking type )
   * - with cabin
   *
   * @param string $bookingCode
   * @return Booking
   */
  public function getBookingByCode($bookingCode, $user = null)
  {
    $user_survivor_number = $user->survivor_number ?? null;

    $booking = Booking::with("passengers", "cabin.cabinCategory", "event")
      ->where("booking_code", $bookingCode)
      ->first();

    // if booking is_single_occupancy then do not return other passengers
    if ($booking->is_single_occupancy) {
      $booking->passengers = $booking->passengers->filter(function ($passenger) use ($user_survivor_number) {
        return $passenger->survivor_number === $user_survivor_number;
      });
    }

    return $booking;
  }

  /**
   * Get all bookings related to the user
   * - with passengers ( depends on the booking type )
   * - with cabin
   *
   * @return Booking
   */
  public function getAllBookings($user)
  {
    $user_survivor_number = $user->survivor_number ?? null;
    $customer_id = $user->id ?? null;

    $bookings = Booking::with("passengers", "cabin.cabinCategory", "event")->where("customer_id", $customer_id)->get();

    // if booking is_single_occupancy then do not return other passengers
    $bookings->map(function ($booking) use ($user_survivor_number) {
      if ($booking->is_single_occupancy) {
        $booking->passengers = $booking->passengers->filter(function ($passenger) use ($user_survivor_number) {
          return $passenger->survivor_number === $user_survivor_number;
        });
      }
    });

    return $bookings;
  }

  // create passenger
  public function createPassenger($data)
  {
    return Passenger::create($data);
  }
}
