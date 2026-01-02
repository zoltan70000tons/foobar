<?php

namespace App\Interfaces;

use App\Models\Cabin;

interface LogInterface {
    function getLogsByBookingId(int $bookingId);
    function getCommentsById(int $bookingId);
    function getHistory(int $bookingId);
}
