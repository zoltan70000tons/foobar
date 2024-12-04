<?php

namespace App\Interfaces;

use App\Models\Booking;
use App\Models\Cabin;
use App\Models\Passenger;

interface PassengerInterface
{
    function create(array $data, Booking $booking): Passenger|bool;
 
}
