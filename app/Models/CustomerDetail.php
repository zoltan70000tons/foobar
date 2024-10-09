<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CustomerDetail extends Model
{
  use HasFactory;

  protected $fillable = [
    'customer_id',
    'gender',
    'first_name',
    'middle_name',
    'last_name',
    'dob',
    'citizenship',
    'phone',
    'avatar',
    'emergency_c_name',
    'emergency_c_phone',
    'language',
  ];

  /**
   * Get the customer that owns the details.
   */
  public function customer()
  {
    return $this->belongsTo(User::class);
  }

  /**
   * Set the customer's date of birth.
   *
   * @param  string  $value
   * @return void
   * @throws \Exception
   */
  public function setDobAttribute($value)
  {
    // Parse the date and check if it's in the future
    $dob = Carbon::parse($value);

    if ($dob->isFuture()) {
      throw new \Exception('The date of birth cannot be in the future.');
    }
    
    // Set the dob attribute in 'm-d-Y' format
    $this->attributes['dob'] = $dob->format('m-d-Y');
  }
}
