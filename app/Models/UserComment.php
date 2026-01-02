<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserComment extends Model {
    use HasFactory, SoftDeletes;

    protected $table = 'user_comments';
    protected $fillable = ['customer_id', 'author_id', 'comment'];
    protected array $dates = ['deleted_at']; // Make sure deleted_at is cast as a date

    public function author(): BelongsTo {
        return $this->belongsTo(User::class, 'author_id')->latest();
    }
}
