<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class CabinCategory extends Model
{
    use HasFactory;

    protected $table = 'cabin_categories';

    protected $primaryKey = 'id';

    protected $fillable = [
        'category_type',
        'category_code',
        'category_name',
        'capacity',
        'description',
        'images',
        'iframe',
        'price',
        'display_order',
        'cruise_id',
        'event_id',
    ];

    protected $casts = [
        'images' => 'array',
        'price' => 'decimal:2',
    ];

    // Relations
    public function cabins()
    {
        return $this->hasMany(Cabin::class, 'cabin_category_id');
    }

    public function cruise()
    {
        return $this->belongsTo(Cruise::class, 'cruise_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

}
