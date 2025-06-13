<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Fee extends Model
{
  use HasApiTokens, HasFactory;

  protected $table = 'fees';

  protected $fillable = [
    'passenger_id',
    'type',
    'amount',
    'notes',
  ];

  // Use the 'temp_due_date' attribute to store alternate due date on installment creation
  protected $appends = ['temp_due_date'];
  protected $hidden = ['temp_due_date'];
  public $temp_due_date = null;

  protected static function boot()
  {
    parent::boot();

    static::creating(function ($fee) {});

    static::created(function ($fee) {
      Installment::create([
        'due_date' => $fee->temp_due_date ?? now(),
        'passenger_id' => $fee->passenger_id,
        'type' => 'FEE',
        'fee_id' => $fee->id,
      ]);
    });

    static::updated(function ($fee) {});

    static::deleted(function ($fee) {});
  }

  public function passenger()
  {
    return $this->belongsTo(Passenger::class, 'passenger_id');
  }
}
