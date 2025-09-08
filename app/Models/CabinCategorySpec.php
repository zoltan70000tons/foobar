<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CabinCategorySpec extends Model
{
  use HasFactory;

  protected $table = 'cabin_category_specs';

  protected $primaryKey = 'id';

  protected $fillable = [
    'category_type',
    'category_code',
    'category_name',
    'capacity',
    'description',
    'iframe',
    'images',
    'decks',
    'display_order',
    'cruise_id',
    'category_number',
  ];

  protected $casts = [
    'description' => 'array', // JSONB field for multi-language descriptions
    'images' => 'array', // JSON field for storing images as an array
  ];

  // Relations
  public function cabinCategories()
  {
    return $this->hasMany(CabinCategory::class, 'cabin_category_spec_id');
  }

  public function cruise()
  {
    return $this->belongsTo(Cruise::class, 'cruise_id');
  }

  public function getFirstLetterOfCategoryType(): string
  {
      return substr($this->category_type, 0, 1);
  }
}
