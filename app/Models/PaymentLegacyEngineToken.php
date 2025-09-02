<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentLegacyEngineToken extends Model
{
    protected $fillable = [
        'token', 'email', 'data', 'expires_at', 'used'
    ];

    protected $casts = [
        'data' => 'array',
        'expires_at' => 'datetime',
        'used' => 'boolean',
    ];
}
