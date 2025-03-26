<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class PassengerDiscount extends Model
{
  use HasApiTokens, HasFactory;

  protected $table = 'passenger_discounts';

  protected $fillable = [
    'passenger_id',
    'type',
    'amount',
    'operation'
  ];

  public function passenger()
  {
    return $this->belongsTo(Passenger::class, 'passenger_id');
  }
}
