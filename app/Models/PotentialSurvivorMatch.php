<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PotentialSurvivorMatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'passenger_id',
        'passenger_first_name',
        'passenger_last_name',
        'passenger_dob',

        'user_detail_id',
        'user_first_name',
        'user_last_name',
        'user_dob',

        'score',
        'status',
        'type',
        'review_date',
        'reviewer_id',
    ];
    protected $casts = [
        'score' => 'float',
        'review_date' => 'datetime',
    ];
    protected $appends = [
        'passenger_name',
        'user_name',
    ];

    public function getPassengerNameAttribute(): string
    {
        return trim("{$this->passenger_first_name} {$this->passenger_last_name}");
    }

    public function getUserNameAttribute(): string
    {
        return trim("{$this->user_first_name} {$this->user_last_name}");
    }

    public function passenger()
    {
        return $this->belongsTo(Passenger::class);
    }

    public function userDetail()
    {
        return $this->belongsTo(UserDetail::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
