<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemporaryReservation extends Model {
    use HasFactory;

    protected $fillable = ['cabin_id', 'user_id', 'cabin_number', 'expires_at', 'inventory'];

    // Define the relationship with Cabin
    public function cabin() {
        return $this->belongsTo(Cabin::class);
    }

    // Define the relationship with User (optional)
    public function user() {
        return $this->belongsTo(User::class);
    }

    // Accessor to get the cabin type ID
    public function getCabinTypeIdAttribute() {
        return $this->cabin->cabin_type_id ?? null;
    }

    // Accessor to get the cabin category ID
    public function getCabinCategoryIdAttribute() {
        return $this->cabin->cabin_category_id ?? null;
    }
}
