<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Payment extends Model
{
  use HasApiTokens, HasFactory;

  protected $table = 'payments';

  protected $fillable = [
    'passenger_id',
    'BIP_ID',
    'type',
    'transaction_date',
    'amount',
    'notes',
    'source'
  ];

  protected static function boot()
  {
    parent::boot();
    static::created(function ($payment) {
     
    });
  }
}
