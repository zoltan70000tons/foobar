<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\BookingLog;
use App\Traits\BookingLogTrait;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Hidehalo\Nanoid\Client;
use Illuminate\Support\Facades\DB;

class Booking extends Model
{
  use HasFactory;
  use BookingLogTrait;

  protected $fillable = [
    'booking_code',
    'booking_request_id',
    'event_id',
    'customer_id',
    'payment_plan',
    'cabin_id',
    'is_single_occupancy',
    'bed_config',
    'agent_id',
    'status',
    'tags',
  ];

  protected $casts = [
    'tags' => 'json',
  ];

  /**
   * Relationship: A booking belongs to many adjustments.
   */
  public function adjustments(): BelongsToMany
  {
    return $this->belongsToMany(Adjustment::class, 'booking_has_adjustments');
  }

  /**
   * Relationship: A booking belongs to a customer.
   */
  public function customer()
  {
    return $this->belongsTo(User::class, 'customer_id');
  }

  /**
   * Relationship: A booking have one event id.
   */
  public function event()
  {
    return $this->belongsTo(Event::class, 'event_id');
  }

  /**
   * Relationship: A booking belongs to a customer.
   */
  public function agent()
  {
    return $this->belongsTo(User::class, 'agent_id');
  }

  /**
   * Relationship: A booking belongs to one cabin (one-to-one).
   */
  public function cabin()
  {
    return $this->belongsTo(Cabin::class, 'cabin_id');
  }

  public function passengers()
  {
    return $this->hasMany(Passenger::class, 'booking_id')->orderBy('passenger_order');
  }

  /**
   * Assign a cabin to the booking and update the cabin's inventory and status.
   *
   * @param  \App\Models\Cabin  $cabin
   * @return void
   * @throws \Exception if the cabin is already fully booked or has no available inventory
   */

  public function assignCabin(Cabin $cabin): array|bool
  {
    try {
      // Validate that the cabin has available inventory
      if ($cabin->inventory <= 0) {
        throw new \Exception('This cabin has no available inventory.');
      }

      // Check if the cabin is already fully booked
      if (strtoupper($cabin->status) === 'BOOKED') {
        throw new \Exception('This cabin is already fully booked.');
      }

      // Assign the cabin to this booking
      $this->cabin_id = $cabin->id;

      // Save the updated booking
      $this->save();

      // Update the cabin's inventory and status
      $cabin->updateInventoryOnBooking();

      // Return true if successful
      return true;
    } catch (\Exception $e) {
      // Return an error array in case of exception
      return [
        'error' => true,
        'message' => $e->getMessage(),
      ];
    }
  }

  public function logs()
  {
    return $this->hasMany(BookingLog::class, 'booking_id', 'id');
  }

  public function comments()
  {
    return $this->hasMany(Comment::class, 'booking_id', 'id');
  }

  public function lockedBy()
  {
    return $this->hasOne(BookingAgentSessions::class, 'booking_id', 'id');
  }

  public function changeCabin($number)
  {
    try {
      // Find the cabin by its number
      $cabin = Cabin::whereHas('cabinSpec', function ($query) use ($number) {
        $query->where('cabin_number', $number);
      })->first();
      // Validate that the cabin exists
      if (!$cabin) {
        throw new \Exception("Cabin with number {$number} does not exist.");
      }

      // Check if the cabin is available
      if ($cabin->inventory <= 0) {
        throw new \Exception('This cabin has no available inventory.');
      }

      if (strtoupper($cabin->status) === 'BOOKED') {
        throw new \Exception('This cabin is already fully booked.');
      }

      // Get the previous cabin
      $prevCabin = $this->cabin;

      // Check if the cabin is of the same type as the current booking
      if ($cabin->cabinType->id !== $prevCabin->cabinType->id) {
        throw new \Exception('This cabin is not of the same type as the current booking.');
      }

      // Release the previous cabin
      $prevCabin->releaseCabin();

      // Assign the new cabin to the booking
      $this->assignCabin($cabin);

      // Generate a new booking code
      $preBookingCode = $this->booking_code;
      $this->booking_code = $this->generateBookingCode($cabin);
      $this->save();

      // Save the booking log
      $this->saveBookingLog(
        $this->id,
        'Changed Cabin',
        "Cabin changed from  {$prevCabin->cabin_number} to {$cabin->cabin_number}. /n Booking code changed from {$preBookingCode} to {$this->booking_code}"
      );

      return $this;
    } catch (\Exception $e) {
      return ['error' => $e->getMessage()];
    }
  }

  protected function containsBlockedWords($code)
  {
    $blocked_words = config('whitelist.blocked_words');

    // Check against blocked words
    return in_array(strtoupper($code), $blocked_words);
  }

  public function cancel()
  {
    try {
      if ($this->status === 'CANCELLED') {
        throw new \Exception('This booking is already cancelled.');
      }
      $segments = explode('-', $this->booking_code);

      if (count($segments) !== 2) {
        throw new \Exception('Invalid booking code format.');
      }
      $characters = config('whitelist.allowed_characters');
      do {
        $randomSegment = substr(str_shuffle($characters), 0, 4);
      } while ($this->containsBlockedWords($randomSegment));
      $segments[1] = $randomSegment;
      $this->booking_code = implode('-', $segments);
      $this->status = 'CANCELLED';
      $this->save();

      return true;
    } catch (\Exception $e) {
      \Log::error('Error cancelling booking: ' . $e->getMessage());
      return false;
    }
  }

  /**
   * Generate a unique booking code for this booking.
   */
  private function generateBookingCode(Cabin $cabin): string
  {
    $characters = config('whitelist.allowed_characters');
    $year = 'F';
    //$categoryLetter = strtoupper(chr(64 + $cabin->category->display_order));
    // Generate a random 4-character code
    do {
      $identifier_code = substr(str_shuffle($characters), 0, 4);
    } while ($this->containsBlockedWords($identifier_code));

    return "{$cabin->cabin_number}{$identifier_code}-{$year}1{$cabin->category->category_number}{$cabin->category->category_code}";
  }

  protected static function boot()
  {
    parent::boot();

    static::creating(function ($booking) {
      if (!$booking->booking_code && $booking->cabin) {
        $booking->booking_code = $booking->generateBookingCode($booking->cabin);
      }

      // @JG BOOKING REQUEST CODE
      if (!$booking->booking_request_id) {
        $client = new Client();
        $booking->booking_request_id = $client->formattedId('0123456789ABCDEFGHIJKLMNOPERSTUWXYZ', 10);
      }
      // Set status to 'NEW' if not already set
      if (!$booking->status) {
        $booking->status = 'NEW';
      }
    });

    static::created(function ($booking) {
      $booking->saveBookingLog($booking->id, 'Created', 'The booking was created');
      if ($booking->cabin && $booking->cabin->id) {
        $booking->cabin->updateInventoryOnBooking();
      }
    });

    static::updated(function ($booking) {
      if (!$booking->isDirty('status')) {
        return;
      }

      $previousStatus = $booking->getOriginal('status');
      $newStatus = $booking->status;
      if ($newStatus === 'CANCELLED') {
        $cabin = $booking->cabin;
        $cabinTypeId = $cabin->cabinType->id;
        $cabin->inventory += 1;
        if ($cabinTypeId === 1) {
          $cabin->status = 'AVAILABLE';
        } else {
          $capacity = $cabin->category->capacity;
          $cabin->status = $cabin->inventory == $capacity ? 'AVAILABLE' : 'PARTIALLY_BOOKED';
        }
        $cabin->save();
        //deleting booking from locks table
        DB::table('booking_agent_sessions')
          ->where('booking_id', $booking->id)
          ->delete();
      }
    });

    static::deleted(function ($booking) {
      $booking->saveBookingLog($booking->id, 'Deleted', 'The booking was deleted');
    });
  }

  public function getTotalpaid()
  {
    return $this->passengers->sum('passenger_balance');
  }

  public function getGrandTotal()
  {
    return $this->passengers->sum('passenger_allocated_cost');
  }
}
