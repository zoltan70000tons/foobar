<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemporaryPassword extends Model
{
    protected $fillable = ['customer_id', 'generated_by', 'temporary_password', 'expires_at'];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
