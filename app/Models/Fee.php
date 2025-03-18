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
    'amount'
  ];



  protected static function boot()
  {
    parent::boot();

    static::creating(function ($fee) {});

    static::created(function ($fee) {
      $installment = Installment::create(array('due_date' => now(), 'passenger_id' =>  $fee->passenger_id, 'type' => 'FEE',  'fee_id'=> $fee->id));
    });

    static::updated(function ($fee) {});

    static::deleted(function ($fee) {});
  }

}
