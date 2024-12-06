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
   * - with passengers
   * - with cabin
   *
   * @param string $bookingCode
   * @return Booking
   */

  public function getBookingByCode($bookingCode)
  {
    return Booking::with("passengers", "cabin.cabinCategory")->where("booking_code", $bookingCode)->first();
  }
}
