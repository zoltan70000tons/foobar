<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
  use HasFactory;

  protected $fillable = ['user_id', 'cart_data'];

  // Automatically converts JSON to an array
  protected $casts = [
    'cart_data' => 'array',
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }
}
