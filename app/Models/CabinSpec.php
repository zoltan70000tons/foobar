<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CabinSpec extends Model {
    use HasFactory;

    protected $table = 'cabin_specs';

    protected $primaryKey = 'id';

    protected $fillable = [
        'cabin_number',
        'deck',
        'total_berths',
        'lower_bed_type_1',
        'lower_bed_type_2',
        'upper_berths',
        'accessible',
        'connects_with',
        'location',
        'balcony',
        'obstructed_view',
    ];

    protected $casts = [
        'accessible' => 'boolean',
        'balcony' => 'boolean',
        'obstructed_view' => 'boolean',
    ];
    protected $appends = ['is_shared_cabin_number'];

    // Relations
    public function cabins() {
        return $this->hasMany(Cabin::class, 'cabin_spec_id');
    }

    public function connectingCabin() {
        return $this->belongsTo(CabinSpec::class, 'connects_with');
    }

    /**
     * Get the 'is_shared_cabin_number' attribute.
     * @return bool
     */
    public function getIsSharedCabinNumberAttribute(): bool {
        if ($this->relationLoaded('cabins')) {
            return $this->cabins->count() > 1;
        }

        // Fallback if not eager loaded (avoids surprise N+1 when not loaded)
        return $this->cabins()->count() > 1;
    }
}
