<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'address',
        'phone',
        'first_name',
        'middle_name',
        'last_name',
        'gender'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
