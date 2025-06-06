<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PresalePeriod extends Model
{
  use HasFactory;

  protected $table = 'presale_periods';

  protected $fillable = [
    'id',
    'event_id',
    'membership_type_id',
    'start_date',
    'end_date',
  ];

  protected $casts = [
    'start_date' => 'datetime',
    'end_date' => 'datetime',
  ];

  public function membershipType()
  {
    return $this->belongsTo(MembershipType::class);
  }

  public function event()
  {
    return $this->belongsTo(Event::class);
  }
}
