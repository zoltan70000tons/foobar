<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserTag extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'description', 'color'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_has_tags', 'tag_id', 'user_id');
    }
}
