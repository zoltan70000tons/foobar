<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string|null $name
 * @property string|null $description
 * @property string|null $image
 * @property string|null $address
 */
class Event extends Model {
    use HasFactory;

    protected $table = 'events';

    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'description',
        'image',
        'address',
        'start_date',
        'end_date',
        'status',
        'booked_stamp',
        'url',
        'organization_id',
    ];

    protected $dates = ['start_date', 'end_date'];

    // Relationships
    public function cabinCategories() {
        return $this->hasMany(CabinCategory::class, 'cruise_id');
    }

    public function organization() {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function bookings() {
        return $this->hasMany(Booking::class, 'event_id');
    }

    public function adjustments() {
        return $this->hasMany(Adjustment::class);
    }

    public function presalePeriods() {
        return $this->hasMany(PresalePeriod::class, 'event_id');
    }
}
