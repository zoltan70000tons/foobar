<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CabinChange implements ShouldBroadcastNow
{
  use Dispatchable, InteractsWithSockets, SerializesModels;

  public $cabins;

  /**
   * Create a new event instance.
   */
  public function __construct($cabins = null)
  {
    // Set default data if $cabins is null
    if (is_null($cabins)) {
      $this->cabins = [
        [
          'cabin_number' => '0000',
          'cabin_status' => 'available',
        ],
      ];
    } else {
      $this->cabins = $cabins->map(function ($cabin) {
        return [
          'cabin_number' => $cabin->cabin_number,
          'cabin_status' => $cabin->cabin_status,
        ];
      })->toArray();
    }
  }

  /**
   * Get the channels the event should broadcast on.
   *
   * @return array<int, \Illuminate\Broadcasting\Channel>
   */
  public function broadcastOn(): array
  {
    return [
      new Channel('channel-for-everyone'),
    ];
  }

  /**
   * Get the data to broadcast.
   *
   * @return array<string, string>
   */

  public function broadcastWith(): array
  {
    return [
      'cabins' => $this->cabins,
    ];
  }
}
