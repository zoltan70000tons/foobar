<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\UUID;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;

use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements CanResetPassword
{
  use CanResetPasswordTrait, HasFactory, HasRoles, Notifiable, HasApiTokens, UUID;

  protected $guard_name = "web";

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = ["email", "password", "phone", "organization_id"];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  protected $hidden = ["password", "remember_token"];

  /**
   * Get the attributes that should be cast.
   *
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      "email_verified_at" => "datetime",
      "password" => "hashed",
    ];
  }

  // bookings
  public function bookings()
  {
    return $this->hasMany(Booking::class, "customer_id");
  }

  // Membership types
  public function membershipTypes()
  {
    return $this->belongsToMany(MembershipType::class, "memberships", "user_id", "membership_id");
  }

  // Organizations
  public function organizations(): BelongsToMany
  {
    return $this->belongsToMany(Organization::class)
      ->withPivot("user_id")
      ->withTimestamps();
  }

  // teams
  public function teams()
  {
    return $this->belongsToMany(Team::class, "team_user", "user_id", "team_id");
  }

  // detail
  public function detail(): HasOne
  {
    return $this->hasOne(UserDetail::class, "user_id");
  }

  // survivor number
  public function survivorNumber()
  {
    return $this->hasOne(SurvivorNumber::class);
  }

  // Customer address
  public function customerAddress(): HasOne
  {
    return $this->hasOne(CustomerAddress::class, "user_id");
  }

  public function memberShip(){
    return $this->hasOne(Membership::class, foreignKey:'user_id');
  }
}
