<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CabinType extends Model
{
    protected $table = 'cabin_types';
    
    protected $fillable = ['cabin_type'];
    
    // Relations
	public function cabinCategories()
	{
		return $this->hasMany(Cabin::class, 'cabin_type_id');
	}
}
