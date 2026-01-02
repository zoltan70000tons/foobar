<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PassengerToken extends Model {
    protected $fillable = ['passenger_id', 'token_id', 'expires_at'];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function passenger() {
        return $this->belongsTo(Passenger::class);
    }
}
