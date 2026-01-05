<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model {
    public $timestamps = false;
    protected $table = 'logs';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'action',
        'actor_type',
        'actor_id',
        'related_type',
        'related_id',
        'description',
        'payload',
        'created_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'created_at' => 'datetime',
    ];

    public function related() {
        return $this->morphTo(__FUNCTION__, 'related_type', 'related_id');
    }

    public function actor() {
        return $this->belongsTo(User::class, 'actor_id', 'id');
    }
}
