<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\Channel;

class BookingLocked
{
    use Dispatchable, SerializesModels;

    public $bookingId;

    /**
     * Create a new event instance.
     */
    public function __construct(int $bookingId)
    {
        $this->bookingId = $bookingId;
    }

    public function broadcastOn()
    {
        return new Channel('bookings-locked');
    }
}
