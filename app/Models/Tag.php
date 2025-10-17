<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use HasUuids;

class Tag extends Model
{
    
    protected $table = 'tags';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
    protected $fillable = ['name','type','color','description','is_system'];
    protected $casts = [
        'is_system' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    

    public function bookings()
    {
        return $this->morphedByMany(Booking::class, 'entity', 'taggings', 'tag_id', 'entity_id')
            ->withPivot('created_at');
    }

    public function cabins()
    {
        return $this->morphedByMany(Cabin::class, 'entity', 'taggings', 'tag_id', 'entity_id')
            ->withPivot('created_at');
    }

    public function customers()
    {
        return $this->morphedByMany(User::class, 'entity', 'taggings', 'tag_id', 'entity_id')
            ->withPivot('created_at');
    }

    public function scopeType($q, string $type)   { return $q->where('type', $type); }
    public function scopeSearch($q, string $text) { return $q->whereRaw("search_tsv @@ plainto_tsquery('simple', ?)", [$text]); }
}
