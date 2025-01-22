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


}
