<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemporaryReservation extends Model
{
    use HasFactory;

    protected $fillable = ['cabin_id', 'user_id', 'cabin_number', 'expires_at', 'inventory'];

    // Define the relationship with Cabin
    public function cabin()
    {
        return $this->belongsTo(Cabin::class);
    }

    // Define the relationship with User (optional)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
