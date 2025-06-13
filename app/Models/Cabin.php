<?php

namespace App\Models;

use App\Enums\StatusCabin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Log;

class Cabin extends Model
{
  use HasFactory;

  protected $table = 'cabins';

  protected $primaryKey = 'id';

  protected $fillable = ['cabin_type_id', 'cabin_category_id', 'cabin_spec_id', 'inventory', 'notes', 'internal_notes', 'tags', 'status'];

  // Cast attributes to specific types
  protected $casts = [
    'tags' => 'json', // Tags stored as a JSON array
    'inventory' => 'integer', // Inventory stored as an integer
  ];

  // Append custom attributes to the serialized output
  protected $appends = ['cabin_number', 'deck', 'balcony', 'obstructed_view', 'location', 'accessible'];

  // ==========================
  // Relationships
  // ==========================

  /**
   * Relationship: A cabin belongs to a CabinSpec.
   */
  public function cabinSpec()
  {
    return $this->belongsTo(CabinSpec::class, 'cabin_spec_id');
  }

  /**
   * Relationship: A cabin belongs to a CabinCategory.
   */
  public function category()
  {
    return $this->belongsTo(CabinCategory::class, 'cabin_category_id');
  }

  /**
   * Relationship: A cabin belongs to a CabinType.
   */
  public function cabinType()
  {
    return $this->belongsTo(CabinType::class, 'cabin_type_id');
  }

  /**
   * Relationship: A cabin can have many temporary reservations.
   */
  public function temporaryReservations()
  {
    return $this->hasMany(TemporaryReservation::class);
  }

  /**
   * Relationship: A cabin can have multiple bookings if cabin_type_id is 2 or 3 (single tickets),
   * otherwise one booking (private cabins).
   */
  public function bookings()
  {
    return $this->cabin_type_id === 1
      // One-to-one relationship for private cabins
      ? $this->hasOne(Booking::class, 'cabin_id')
      // One-to-many relationship for single-ticket cabins
      : $this->hasMany(Booking::class, 'cabin_id');
  }

  // ==========================
  // Accessors
  // ==========================
  /**
   * Accessor: Get the cabin number from the related CabinSpec.
   */
  protected function cabinNumber(): Attribute
  {
    return Attribute::get(fn() => $this->cabinSpec ? $this->cabinSpec->cabin_number : null);
  }

  /**
   * Accessor: Get the deck from the related CabinSpec.
   */
  protected function deck(): Attribute
  {
    return Attribute::get(fn() => $this->cabinSpec?->deck ? $this->cabinSpec->deck : null);
  }

  /**
   * Accessor: Get the balcony status from the related CabinSpec.
   */
  protected function balcony(): Attribute
  {
    return Attribute::get(fn() => $this->cabinSpec?->balcony ?? false);
  }

  /**
   * Accessor: Get the obstructed view status from the related CabinSpec.
   */
  protected function obstructedView(): Attribute
  {
    return Attribute::get(fn() => $this->cabinSpec?->obstructed_view ?? false);
  }

  /**
   * Accessor: Get the location from the related CabinSpec.
   */
  protected function location(): Attribute
  {
    return Attribute::get(fn() => $this->cabinSpec?->location ? $this->cabinSpec->location : null);
  }

  /**
   * Accessor: Get the accessibility from the related CabinSpec.
   */
  protected function accessible(): Attribute
  {
    return Attribute::get(fn() => $this->cabinSpec?->accessible ?? null);
  }

  /**
   * Accessor: Get the lower bed type 2 from the related CabinSpec.
   */
  protected function lowerBedType2(): Attribute
  {
    return Attribute::get(fn() => $this->cabinSpec?->lower_bed_type_2 ?? null);
  }

  // ==========================
  // Methods
  // ==========================

  /**
   * Update the inventory and status of the cabin based on a booking.
   */
  public function updateInventoryOnBooking()
  {
    Log::info('Updating inventory on booking');

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

  /**
   * Release the cabin from a booking.
   */
  public function releaseCabin()
  {
    Log::info('Releasing cabin');

    if ($this->cabin_type_id == 1) {
      // Private cabins, mark as available
      $this->status = 'AVAILABLE';
      $this->inventory = 1;
    } elseif (in_array($this->cabin_type_id, [2, 3])) {
      // Single ticket cabins, increase inventory
      $this->inventory++;

      if ($this->inventory == $this->category->capacity) {
        $this->status = 'AVAILABLE';
      } else {
        $this->status = 'PARTIALLY_BOOKED';
      }
    }

    $this->save();
  }

  protected static function booted()
  {
    static::saving(function (Cabin $cabin) {
      if (is_array($cabin->tags) && in_array('RCCL', $cabin->tags) && $cabin->status !== StatusCabin::CLOSED->value) {
        // Force status to "RESERVED" if 'RCCL' tag is present
        $cabin->status = StatusCabin::RESERVED->value;
      }
    });
  }

  protected static function boot()
  {
    parent::boot();
     static::updated(function ($cabin) {
       static::where('cabin_spec_id', $cabin->cabin_spec_id)
      ->where('id', '!=', $cabin->id)
      ->update([
        'cabin_type_id' => $cabin->cabin_type_id,
        'inventory' => $cabin->inventory,
        'status' => $cabin->status,
      ]);
   
    });

  }
}
