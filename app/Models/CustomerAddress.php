<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string|null $address_first
 * @property string|null $address_second
 * @property string|null $city
 * @property string|null $state
 * @property string|null $postal_code
 * @property string|null $country
 */
class CustomerAddress extends Model
{
  use HasFactory;

  protected $fillable = [
    'user_id',
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
    return $this->belongsTo(User::class);
  }
}
