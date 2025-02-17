<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Passenger extends Model
{
  use HasApiTokens, HasFactory;

  protected $table = 'passengers';

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'booking_id',
    'confirmed_booking_email',
    'lead_passenger',
    'survivor_number',
    'payment_method',
    'gender',
    'first_name',
    'middle_name',
    'last_name',
    'dob',
    'citizenship',
    'address_first',
    'address_second',
    'city',
    'state',
    'postal_code',
    'country',
    'email',
    'phone',
    'emergency_c_name',
    'emergency_c_phone',
    'special_request',
    'special_options',
    'hear_about',
    // 'referral_details',
    'newsletter',
    'travel_info',
    'terms_n_cons',
    'empty_seat',
    'cabin_conf_accp',
    'single_t_agreement',
    'passenger_allocated_cost',
    'passenger_order',
    'passenger_balance',
    'was_on_board',
  ];

  protected $appends = ['full_name', 'empty'];

  protected $casts = [
    'special_options' => 'array',
  ];

  /**
   * Relationship: A passenger belongs to a booking.
   */
  public function booking()
  {
    return $this->belongsTo(Booking::class);
  }

  public function getFullNameAttribute()
  {
    return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
  }

  public function getEmptyAttribute()
  {
    if ($this->first_name == null && $this->last_name == null && $this->email === null) {
      return true;
    }
    return false;
  }

  // passenger may have installments
  public function installments()
  {
    return $this->hasMany(Installment::class);
  }

  public function payments()
  {
    return $this->hasMany(Payment::class);
  }

  public function fees()
  {
    return $this->hasMany(Fee::class);
  }
}
