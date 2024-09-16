<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cabin extends Model
{
    use HasFactory;

    protected $table = 'cabins';

    protected $primaryKey = 'id';

    protected $fillable = [
        'cabin_type_id',
        'cabin_category_id',
        'cabin_code',
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
        'inventory',
        'notes',
        'tags',
        'status',
    ];

    protected $casts = [
        'tags' => 'json',
        'accessible' => 'boolean',
        'balcony' => 'boolean',
        'obstrucuted_view' => 'boolean',
    ];
    

    // Relations
    public function category()
    {
        return $this->belongsTo(CabinCategory::class, 'cabin_category_id');
    }

    public function cabinType()
    {
        return $this->belongsTo(CabinType::class, 'cabin_type_id');
    }

    public function connectingCabin()
    {
        return $this->belongsTo(Cabin::class, 'connects_with');
    }
}