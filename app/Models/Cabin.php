<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Log;

class Cabin extends Model
{
  use HasFactory;

  protected $table = 'cabins';

  protected $primaryKey = 'id';

  protected $fillable = [
    'cabin_type_id',
    'cabin_category_id',
    'cabin_number',
    'deck',
    'total_berths',
    'lower_bed_type_1',
    'lower_bed_type_2',
    'upper_berths',
    'accessible',
    'connects_with',
    'location',
    'balcony',
    'obstructed_view',
    'inventory',
    'notes',
    'tags',
    'status',
  ];

  protected $casts = [
    'tags' => 'json',
    'accessible' => 'boolean',
    'balcony' => 'boolean',
    'obstrucuted_view' => 'boolean',
  ];

  // Relations
  public function temporaryReservations()
  {
      return $this->hasMany(TemporaryReservation::class);
  }
  
  public function category()
  {
    return $this->belongsTo(CabinCategory::class, 'cabin_category_id');
  }

  public function cabinCategory()
  {
    return $this->belongsTo(CabinCategory::class, 'cabin_category_id', 'id');
  }

  public function cabinType()
  {
    return $this->belongsTo(CabinType::class, 'cabin_type_id');
  }

  public function connectingCabin()
  {
    return $this->belongsTo(Cabin::class, 'connects_with');
  }

  /**
   * Relationship: A cabin can have multiple bookings if cabin_type_id is 2 or 3 (single tickets),
   * otherwise one booking (private cabins).
   */
  public function bookings()
  {
    if (in_array($this->cabin_type_id, [2, 3])) {
      // One-to-many relationship for single-ticket cabins
      return $this->hasMany(Booking::class, 'cabin_id');
    } else {
      // One-to-one relationship for private cabins
      return $this->hasOne(Booking::class, 'cabin_id');
    }
  }

  /**
   * Mark the cabin as partially sold or sold based on the remaining inventory.
   */
  public function updateInventoryOnBooking()
  {
    Log::info('updateInventory on booking' );
    if ($this->cabin_type_id == 1) {
      // Private cabins, only one booking allowed, mark as sold
      $this->status = 'BOOKED';
      $this->inventory = 0;
    } elseif (in_array($this->cabin_type_id, [2, 3])) {
      // Single ticket cabins, reduce inventory
      $this->inventory--;

      if ($this->inventory > 0) {
        $this->status = 'PARTIALLY_BOOKED';
      } else {
        $this->status = 'BOOKED';
      }
    }

    $this->save();
  }
}
