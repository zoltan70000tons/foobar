<?php

namespace App\Interfaces;

use App\Models\Cabin;

interface LogInterface
{
    function writeOnBooking($id,$action,$user);

}
