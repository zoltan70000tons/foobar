<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property float $amount
 * @property string $reason
 * @property int $passenger_id
 */
class OnboardCredit extends Model
{
  use HasApiTokens, HasFactory;

  protected $table = 'onboard_credit';

  protected $fillable = [
    'amount',
    'reason',
    'passenger_id',
  ];

  public function passenger()
  {
    return $this->belongsTo(Passenger::class, 'passenger_id');
  }
}
