<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Traits\HasRestrictions;

class Adjustment extends Model {
    use HasFactory, HasRestrictions;

    protected $fillable = ['code', 'type', 'operation', 'value', 'restrictions', 'event_id', 'system'];

    protected $casts = [
        'restrictions' => 'array',
    ];

    // Event have many adjustments
    public function event() {
        return $this->belongsTo(Event::class);
    }

    public function bookings(): BelongsToMany {
        return $this->belongsToMany(Booking::class, 'booking_has_adjustments');
    }
}
