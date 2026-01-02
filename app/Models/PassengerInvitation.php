<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PassengerInvitation extends Model {
    protected $table = 'passenger_invitations';

    protected $primaryKey = 'id';

    protected $fillable = ['passenger_id', 'booking_id', 'email', 'token', 'sent_at'];

    // ==========================
    // Relationships
    // ==========================

    /**
     * Relationship: A passenger invitation belongs to a Passenger.
     */
    public function passenger() {
        return $this->belongsTo(Passenger::class, 'passenger_id');
    }

    /**
     * Relationship: A passenger invitation belongs to a Booking.
     */
    public function booking() {
        return $this->belongsTo(Booking::class, 'booking_id');
    }
}
