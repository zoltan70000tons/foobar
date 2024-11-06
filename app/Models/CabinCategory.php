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
        'decks',
        'description',
        'images',
        'iframe',
        'price',
        'display_order',
        'cruise_id',
        'event_id'
    ];

    protected $casts = [
        'images' => 'array',
        'price' => 'decimal:2',
    ];

    protected $appends = [
        'title',
    ];

    // Relations
    public function cabins()
    {
        return $this->hasMany(Cabin::class, 'cabin_category_id', 'id');
    }

    public function cruise()
    {
        return $this->belongsTo(Cruise::class, 'cruise_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function getTitleAttribute()
    {

        $title = $this->category_name;
        if (strpos($this->category_name, $this->category_type) === false) {
            $title .= ' ' . $this->category_type;
        }
        $title .= ' ' . $this->category_code . ' ' . $this->getCapacityDescription();
        return $title;
    }
    protected function getCapacityDescription()
    {
        switch ($this->capacity) {
            case 1:
                return 'Single';
            case 2:
                return 'Double/Twin';
            case 3:
                return 'Triple';
            case 4:
                return 'Quad';
            case 5:
                return 'Quint';
            case 6:
                return 'Sextuple';
            case 7:
                return 'Septuple';
            case 8:
                return 'Octuple';
            default:
                return 'Capacity: ' . $this->capacity . ' Persons';
        }
    }
}
