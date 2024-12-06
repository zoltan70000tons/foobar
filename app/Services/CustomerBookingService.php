<?php

namespace App\Services;

use App\Repositories\CustomerBookingRepository;

class CustomerBookingService
{
  protected $customerBookingRepository;

  public function __construct(CustomerBookingRepository $customerBookingRepository)
  {
    $this->customerBookingRepository = $customerBookingRepository;
  }

  // based on cabin capacity and passengers return available beds
  public function getAvailableBeds($bookingCode)
  {
    $booking = $this->customerBookingRepository->getBookingByCode($bookingCode);

    $cabin = $booking->cabin->cabinCategory->capacity;
    $passengers = $booking->passengers;

    $countOfAvaialble = $cabin - count($passengers);

    return $countOfAvaialble;
  }

  //
}
