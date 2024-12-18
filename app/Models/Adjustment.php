<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Adjustment extends Model
{
  use HasFactory;

  protected $fillable = ["code", "type", "operation", "value", "restrictions"];

  // Event have many adjustments
  public function event()
  {
    return $this->belongsTo(Event::class);
  }

  public function bookings(): BelongsToMany
  {
    return $this->belongsToMany(Booking::class, "booking_has_adjustments");
  }
}
