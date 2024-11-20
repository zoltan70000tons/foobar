<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Adjustment extends Model
{
  use HasFactory;

  protected $fillable = ["code", "type", "operation", "value", "restrictions"];

  // Event have many adjustments
  public function event()
  {
    return $this->belongsTo(Event::class);
  }
}
