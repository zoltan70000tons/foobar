<?php
namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class BookingAgentSession implements ShouldBroadcast {
    use Dispatchable, InteractsWithSockets;

    public function __construct(public string $agentId, public int|null $bookingId, public string|null $username) {
    }

    public $queue = 'reverb';

    public function broadcastWith() {
        return [
            'agentId' => $this->agentId,
            'bookingId' => $this->bookingId,
            'username' => $this->username,
        ];
    }

    public function broadcastOn() {
        return ['reverb-lock-booking'];
    }

    public function broadcastAs() {
        return 'ReverbLockBooking';
    }
}
