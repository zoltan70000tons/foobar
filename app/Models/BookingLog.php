<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class BookingLog extends Model {
    use HasFactory;

    protected $fillable = ['id', 'booking_id', 'user_id', 'action', 'created_at', 'description', 'updated_at'];

    public function booking() {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
