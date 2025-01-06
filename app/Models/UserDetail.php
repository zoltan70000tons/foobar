<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'gender',
        'first_name',
        'dob',
        'middle_name',
        'last_name',
        'citizenship',
        'phone',
        'avatar',
        'emergency_c_name',
        'emergency_c_phone',
        'language'
    ];
    protected $appends = ['full_name', 'short_name'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
    }
    public function getShortNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
