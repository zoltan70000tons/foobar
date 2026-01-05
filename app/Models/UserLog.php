<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserLog extends Model {
    use HasFactory, SoftDeletes;

    protected $table = 'user_logs';
    protected $fillable = ['customer_id', 'author_id', 'action', 'description'];
    protected array $dates = ['deleted_at']; // Make sure deleted_at is cast as a date

    public function author(): BelongsTo {
        return $this->belongsTo(User::class, 'author_id');
    }
}
