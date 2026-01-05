<?php

namespace App\Models;

class Customer extends User {
    protected $table = 'users';
    protected $keyType = 'string';
    public $incrementing = false;

    public function tags() {
        return $this->morphToMany(Tag::class, 'entity', 'taggings', 'entity_id', 'tag_id')->withPivot('created_at');
    }
}
