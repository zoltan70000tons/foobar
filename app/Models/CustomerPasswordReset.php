<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class CustomerPasswordReset extends Model
{
  use HasFactory;

  protected $fillable = [
    'customer_id',
    'token',
  ];

  /*
    * Create a new token for the customer.
    *
    * @param  \App\Models\Customer  $customer
    * @return string
  */
  public static function createToken($customer)
  {
    $token = Str::random(60);

    static::updateOrCreate(
      ['customer_id' => $customer->id],
      [
        'token' => Hash::make($token),
        'created_at' => Carbon::now(),
      ]
    );

    return $token;
  }

  /*
    * Get the customer that the token belongs to.
    *
    * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
  */
  public function customer()
  {
    return $this->belongsTo(Customer::class);
  }

  /*
     * Check if the token is still valid within the 15-minute window.
     *
     * @return bool
     */
  public function isTokenValid()
  {
    return $this->created_at->addMinutes(15)->isFuture();
  }
}
