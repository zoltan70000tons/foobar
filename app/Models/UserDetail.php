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
        'middle_name',
        'last_name',
        'phone',
        'avatar',
        'emergency_c_name',
        'emergency_c_phone'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
