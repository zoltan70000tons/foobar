<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Installment extends Model
{
  protected $table = 'installments';

  protected $fillable = ['passenger_id', 'due_date'];
}
