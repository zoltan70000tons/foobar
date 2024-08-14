<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasRoles;
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'organization_id'
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
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class)->withPivot('user_id')->withTimestamps();
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_user', 'user_id', 'team_id');
    }

    public function detail(): HasOne
    {
        return $this->hasOne(UserDetail::class, 'user_id');
    }

    // static function generateUniqueSurvivorNumber(): int
    // {
    //     do {
    //         $number = '9';
    //         for ($i = 1; $i < 11; $i++) {
    //             $number .= mt_rand(0, 9);
    //         }
    //         $number = (int) $number;
    //         $exists = User::where('survivor_number', $number)->exists();
    //     } while ($exists);
    //     return $number;
    // }

    // protected static function boot()
    // {
    //     parent::boot();
    //     static::creating(function ($user) {
    //         if (empty($user->survivor_number)) {
    //             $user->survivor_number = self::generateUniqueSurvivorNumber();
    //         }
    //     });
    // }
}
