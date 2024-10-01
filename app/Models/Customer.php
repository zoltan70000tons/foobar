<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\CustomerEmailVerification;
use App\Traits\UUID;

class Customer extends Authenticatable implements MustVerifyEmail
{

  use HasFactory, Notifiable, HasApiTokens, HasRoles, UUID;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'name',
    'email',
    'survivor_number',
    'password',
    'policy',
  ];


  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  protected $hidden = [
    'password',
    'remember_token',
  ];

  /**
   * Get the attributes that should be cast.
   *
   * @return array<string, string>
   */
  protected $casts = [
    'email_verified_at' => 'datetime',
    'password' => 'hashed',
    'policy' => 'boolean',
  ];

  /**
   * Define the unique rules for validation.
   *
   * @return array
   */
  public static function rules()
  {
    return [
      'name' => 'required|unique:customers,name',
      'survivor_number' => 'required|unique:customers,survivor_number',
      'password' => 'required',
    ];
  }

  /**
   * Define relationship with the customer's membership type, details, addresses, and bookings.
   * 
   * @return \Illuminate\Database\Eloquent\Relations\HasOne
   */
  public function membershipTypes()
  {
    return $this->belongsToMany(MembershipType::class, 'memberships', 'customer_id', 'membership_id');
  }

  public function details()
  {
    return $this->hasOne(CustomerDetail::class);
  }

  public function addresses()
  {
    return $this->hasMany(CustomerAddress::class);
  }

  public function bookings()
  {
    return $this->hasMany(Booking::class);
  }

  /**
   * Send the email verification notification.
   *
   * @return void
   */
  public function sendEmailVerificationNotification()
  {
    $this->notify(new CustomerEmailVerification());
  }
}
