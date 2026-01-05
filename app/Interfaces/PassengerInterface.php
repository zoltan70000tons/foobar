<?php

namespace App\Interfaces;

use App\Models\Booking;
use App\Models\Cabin;
use App\Models\Passenger;

interface PassengerInterface {
    function create(array $data, Booking $booking): Passenger|bool;
    function find(int $event_id, int $passenger_id, int $booking_id): Passenger|bool;
}
