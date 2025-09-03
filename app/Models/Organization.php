<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Organization extends Model
{
    use HasFactory;

    protected $table = 'organizations';
    protected $fillable = ['name','terms'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('user_id')->withTimestamps();
    }
    
}
