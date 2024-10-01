<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Customer;

class Booking extends Model
{
  use HasFactory;

  protected $fillable = [
    'booking_code',
    'customer_id',
    'payment_method',
    'carbon_offset',
    'cabin_id',
    'completed',
  ];

  /**
   * Relationship: A booking belongs to a customer.
   */
  public function customer()
  {
    return $this->belongsTo(Customer::class, 'customer_id');
  }

  /**
   * Relationship: A booking belongs to one cabin (one-to-one).
   */
  public function cabin()
  {
    return $this->belongsTo(Cabin::class, 'cabin_id');
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
      throw new Exception("This cabin has no available inventory.");
    }

    // Check if the cabin's status is "BOOKED"
    if (strtoupper($cabin->status) === 'BOOKED') {
      throw new Exception("This cabin is already fully booked.");
    }
    
    // Get Lead Passenger Information
    $lead_passenger = Customer::find($this->customer_id);
    $lead_passenger_details = $lead_passenger->details;

    // Define a set of A-Z characters
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    // Generate a random 4-character code
    $identifier_code = substr(str_shuffle($characters), 0, 4);

    // Generate a booking code based on the cabin number, random 4 char string, and the lead passenger's name
    $this->booking_code = $cabin->cabin_number . $identifier_code . '_' . $lead_passenger_details->last_name;

    // Assign the cabin to this booking
    $this->cabin_id = $cabin->id;

    $this->save();

    // Update the cabin inventory and status
    $cabin->updateInventoryOnBooking();
  }
}
