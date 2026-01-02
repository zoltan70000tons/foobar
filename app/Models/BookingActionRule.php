<?php
// app/Models/BookingActionRule.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class BookingActionRule extends Model {
    use HasUuids;

    protected $fillable = [
        'event_id',
        'action_code',
        'applies_from_date',
        'applies_until_date',
        'fee_amount',
        'is_blocking',
        'description',
    ];

    protected $casts = [
        'applies_from_date' => 'datetime',
        'applies_until_date' => 'datetime',
        'is_blocking' => 'boolean',
        'fee_amount' => 'decimal:2',
    ];

    public function scopeActive($query) {
        $now = now();
        return $query
            ->where(function ($q) use ($now) {
                $q->whereNull('applies_from_date')->orWhere('applies_from_date', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('applies_until_date')->orWhere('applies_until_date', '>=', $now);
            });
    }
}
