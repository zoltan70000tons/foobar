<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class OnboardCredit extends Model
{
  use HasApiTokens, HasFactory;

  protected $table = 'onboard_credit';

  protected $fillable = [
    'amount',
    'reason',
    'passenger_id',
  ];

  protected static function boot() {}

  public function passenger()
  {
    return $this->belongsTo(Passenger::class, 'passenger_id');
  }
}
