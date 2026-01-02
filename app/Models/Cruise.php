<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cruise extends Model {
    use HasFactory;

    protected $table = 'cruises';

    // More Fields with ship information might be added in the future -- Carlos
    protected $fillable = ['name'];

    // Relations
    public function cabinCategories() {
        return $this->hasMany(CabinCategory::class, 'event_id');
    }
}
