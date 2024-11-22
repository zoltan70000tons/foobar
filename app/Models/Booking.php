<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\BookingLog;

class Booking extends Model
{
  use HasFactory;

  protected $fillable = [
    "booking_code",
    "customer_id",
    "payment_method",
    "carbon_offset",
    "cabin_id",
    "completed",
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
  public function assignCabin(Cabin $cabin)
  {
    // Check if the cabin's inventory is positive
    if ($cabin->inventory <= 0) {
      throw new \Exception("This cabin has no available inventory.");
    }


    // Check if the cabin's status is "BOOKED"
    if (strtoupper($cabin->status) === "BOOKED") {
      throw new \Exception("This cabin is already fully booked.");
    }

    // Define a set of A-Z characters
    $characters = config('whitelist.allowed_characters');

    // Generate a random 4-character code
    do {
      $identifier_code = substr(str_shuffle($characters), 0, 4);
    } while ($this->containsBlockedWords($identifier_code));
    $year = 'E';

    // Generate a unique booking code based on the cabin number, identifier code, cabin_type_id, category code, and capacity. e.g. "1234-ABCD-14B2"
    //$this->booking_code = "{$cabin->cabin_number}-{$identifier_code}-{$cabin->cabin_type_id}{$cabin->category->category_code}{$cabin->category->capacity}";

    $this->booking_code = "{$cabin->cabin_number}-{$identifier_code}-{$year}{$cabin->category->category_code}{$cabin->category->category_number}{$cabin->category->capacity}";  //Year-Product Identifier - categorie (interior,Promenade) - ocupancy
    // Assign the cabin to this booking
    //Following segment:
    // Category(
    //1 = Interior incl. Promenade, 
    //2 = Window, 
    //3 = Balcony, 
    //4 = Panoramic Suite without Balcony, 
    //5 = Suite with Balcony)
    $this->cabin_id = $cabin->id;

    $this->save();

    // Update the cabin inventory and status
    $cabin->updateInventoryOnBooking();
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

      // If a cabin is already assigned, release its inventory
      if ($this->cabin) {
        // $this->cabin->updateInventoryOnCancellation();
      }
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


    // Log the cabin change action
    // $this->logs()->create([
    //     'user_id' => auth()->id(),
    //     'action' => 'Changed Cabin',
    //     'description' => "Cabin changed to {$cabin->cabin_number}.",
    // ]);
  }

  protected function containsBlockedWords($code)
  {
    $blocked_words = config('whitelist.blocked_words');

    // Check against blocked words
    return in_array(strtoupper($code), $blocked_words);
  }
}
