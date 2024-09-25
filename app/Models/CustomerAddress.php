<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerAddress extends Model
{
  use HasFactory;

  protected $fillable = [
    'customer_id',
    'address_first',
    'address_second',
    'city',
    'state',
    'postal_code',
    'country',
  ];

  /**
   * Get the customer that owns the address.
   */
  public function customer()
  {
    return $this->belongsTo(Customer::class);
  }
}
