<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\BookingLog;
use App\Traits\BookingLogTrait;

class Booking extends Model
{
  use HasFactory;
  use BookingLogTrait;

  protected $fillable = [
    "booking_code",
    "customer_id",
    "payment_method",
    "carbon_offset",
    "cabin_id",
    "completed",
    "event_id",
    "tags",
  ];

  protected $casts = [
    "tags" => "json",
  ];

  /**
   * Relationship: A booking belongs to a customer.
   */
  public function customer()
  {
    return $this->belongsTo(User::class, "customer_id");
  }

  /**
   * Relationship: A booking have one event id.
   */
  public function event()
  {
    return $this->belongsTo(Event::class, "event_id");
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
    return $this->belongsTo(Cabin::class, "cabin_id");
  }

  public function passengers()
  {
    return $this->hasMany(Passenger::class, foreignKey: "booking_id");
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
        throw new \Exception("This cabin has no available inventory.");
      }

      // Check if the cabin is already fully booked
      if (strtoupper($cabin->status) === "BOOKED") {
        throw new \Exception("This cabin is already fully booked.");
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


  public function changeCabin($booking, $number)
  {
    try {
      // Find the cabin by its number
      $cabin = Cabin::where('cabin_number', $number)->first();

      // Validate that the cabin exists
      if (!$cabin) {
        throw new \Exception("Cabin with number {$number} does not exist.");
      }

      // Check if the cabin is available
      if ($cabin->inventory <= 0) {
        throw new \Exception("This cabin has no available inventory.");
      }

      if (strtoupper($cabin->status) === 'BOOKED') {
        throw new \Exception("This cabin is already fully booked.");
      }

      if (strtoupper($cabin->status) === 'PARTIALLY_BOOKED') {
        throw new \Exception("This cabin is already partially booked.");
      }

      $prevCabin = $booking->cabin;
      $prevCabin->inventory = $prevCabin->inventory + 1;
      $prevCabin->save();
      // Assign the new cabin to the booking
      $this->assignCabin($cabin);
      $blog = new BookingLog();
      $blog->booking_id = $this->id;
      $blog->user_id = auth()->id(); // Obtener el usuario actual
      $blog->action = 'Changed Cabin';
      $blog->description = "Cabin changed to {$cabin->cabin_number}.";
      $blog->save();

      return $this;
    } catch (\Exception $e) {
      return false;
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
        throw new \Exception("This booking is already cancelled.");
      }
      $segments = explode('-', $this->booking_code);

      if (count($segments) !== 3) {
        throw new \Exception("Invalid booking code format.");
      }
      $characters = config('whitelist.allowed_characters');
      do {
        $randomSegment = substr(str_shuffle($characters), 0, 4);
      } while ($this->containsBlockedWords($randomSegment));
      $segments[1] = $randomSegment;
      $this->booking_code = implode('-', $segments);
      $this->status = 'CANCELLED';
      $this->is_cancelled = true;
      $this->save();

      return true;
    } catch (\Exception $e) {
      \Log::error("Error cancelling booking: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Generate a unique booking code for this booking.
   */
  private function generateBookingCode(Cabin $cabin): string
  {
    $characters = config('whitelist.allowed_characters');
    $year = 'E';

    // Generate a random 4-character code
    do {
      $identifier_code = substr(str_shuffle($characters), 0, 4);
    } while ($this->containsBlockedWords($identifier_code));

    return "{$cabin->cabin_number}-{$identifier_code}-{$year}{$cabin->category->category_code}{$cabin->category->category_number}{$cabin->category->capacity}";
  }

  protected static function boot()
  {
    parent::boot();

    static::creating(function ($booking) {
      if (!$booking->booking_code && $booking->cabin) {
        $booking->booking_code = $booking->generateBookingCode($booking->cabin);
      }
      // Set status to 'NEW' if not already set
      if (!$booking->status) {
        $booking->status = 'NEW';
      }
    });

    static::created(function ($booking) {
      $booking->saveBookingLog(
        $booking->id,
        'Created',
        "The booking was created"
      );
    });

    static::deleted(function ($booking) {
      $booking->saveBookingLog(
        $booking->id,
        'Deleted',
        "The booking was deleted"
      );
    });
  }
}
