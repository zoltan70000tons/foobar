<?php

namespace App\Repositories;

use App\Interfaces\LogInterface;
use App\Models\BookingLog;
use App\Models\User;


class LogRepository implements LogInterface
{
    protected $organizationId;
    /**
     * Create a new class instance.
     */
    public function __construct() {}

    public function writeOnBooking($id, $action, $user)
    {
        BookingLog::create(
            array(
                'booking_id' => $id,
                'action' => $action,
                'user_id' => $user->id
            )
        );
    }
}
