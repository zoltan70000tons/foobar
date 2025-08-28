<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\UUID;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;
use Illuminate\Support\Facades\Mail;
use Illuminate\Notifications\Notification;

use App\Mail\CustomerResetPassword;
use App\Mail\RegularResetPassword;

use Laravel\Sanctum\HasApiTokens;

/**
 * @property UserDetail|null $detail
 * @property SurvivorNumber|null $survivorNumber
 * @property CustomerAddress|null $customerAddress
 * @property string|null $email
 * @property string|null $username
 * @property string $id
 *
 */
class User extends Authenticatable implements CanResetPassword
{
  use CanResetPasswordTrait, HasFactory, HasRoles, Notifiable, HasApiTokens, UUID;

  protected $keyType = 'string';
  public $incrementing = false;

  protected $guard_name = 'web';

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = ['email', 'password', 'organization_id'];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  protected $hidden = ['password', 'remember_token'];

  /**
   * Get the attributes that should be cast.
   *
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'email_verified_at' => 'datetime',
      'password' => 'hashed',
    ];
  }

  /**
   * Route notifications for the Slack channel.
   */
  public function routeNotificationForSlack(Notification $notification): mixed
  {
    return '#booking-engine-notifications';
  }

  // bookings
  public function bookings()
  {
    return $this->hasMany(Booking::class, 'customer_id');
  }

  // Membership types
  public function membershipTypes()
  {
    return $this->belongsToMany(MembershipType::class, 'memberships', 'user_id', 'membership_id');
  }

  // Organizations
  public function organizations(): BelongsToMany
  {
    return $this->belongsToMany(Organization::class)
      ->withPivot('user_id')
      ->withTimestamps();
  }

  // teams
  public function teams()
  {
    return $this->belongsToMany(Team::class, 'team_user', 'user_id', 'team_id');
  }

  // detail
  public function detail(): HasOne
  {
    return $this->hasOne(UserDetail::class, 'user_id');
  }

  // survivor number
  public function survivorNumber()
  {
    return $this->hasOne(SurvivorNumber::class, 'user_id');
  }

  // Customer address
  public function customerAddress(): HasOne
  {
    return $this->hasOne(CustomerAddress::class, 'user_id');
  }

  public function memberShip()
  {
    return $this->hasOne(Membership::class, foreignKey: 'user_id');
  }

  public function comments(): HasMany
  {
    return $this->hasMany(UserComment::class, 'customer_id');
  }

  public function logs(): HasMany
  {
    return $this->hasMany(UserLog::class, 'customer_id');
  }

  // public function tags(): belongsToMany
  // {
  //     return $this->belongsToMany(UserTag::class, 'user_has_tags', 'user_id', 'tag_id');
  //         //->withTimestamps() // Include created_at and updated_at from the pivot table
  //         //->withTrashed();  // Include soft deleted tags if necessary
  // }

  // send password
  public function sendPasswordResetNotification($token)
  {
    // front end url
    $url = config('app.frontend_url');
    $email = $this->email;
    $tokenToSend = $url . '/en/password-reset?token=' . $token . '&email=' . $email;

    // internal admin url
    $adminUrl = config('app.url');
    $adminTokenToSend = $adminUrl . '/reset-password/' . $token . '?email=' . urlencode($this->email);

    // return Mail::to($this->email)->queue (new CustomerResetPassword($this, $tokenToSend));

    if ($this->hasRole('Customer')) {
      Mail::to($this->email)->queue(new CustomerResetPassword($this, $tokenToSend));
    } else {
      Mail::to($this->email)->queue(new RegularResetPassword($this, $adminTokenToSend));
    }
  }

  public function tags()
  {
    return $this->morphToMany(Tag::class, 'entity', 'taggings', 'entity_id', 'tag_id')
      ->withPivot('created_at');
  }
}
