<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class CabinCategory extends Model
{
  use HasFactory;

  protected $table = 'cabin_categories';

  protected $primaryKey = 'id';

  protected $fillable = ['price', 'cabin_category_spec_id', 'event_id'];

  protected $casts = [
    'price' => 'decimal:2',
  ];

  // Append custom attributes to the serialized output
  protected $appends = [
    'category_type',
    'category_code',
    'category_name',
    'capacity',
    'description',
    'decks',
    'display_order',
    'category_number',
    'images',
    'iframe',
    'title',
    'capacity_description',
  ];

  // Relations
  public function cabins()
  {
    return $this->hasMany(Cabin::class, 'cabin_category_id', 'id');
  }

  public function spec()
  {
    return $this->belongsTo(CabinCategorySpec::class, 'cabin_category_spec_id');
  }

  public function event()
  {
    return $this->belongsTo(Event::class, 'event_id');
  }

  // ==========================
  // Accessors
  // ==========================
  protected function categoryType(): Attribute
  {
    return Attribute::get(fn() => $this->spec->category_type ?? null);
  }

  protected function categoryCode(): Attribute
  {
    return Attribute::get(fn() => $this->spec->category_code ?? null);
  }

  protected function categoryName(): Attribute
  {
    return Attribute::get(fn() => $this->spec->category_name ?? null);
  }

  protected function capacity(): Attribute
  {
    return Attribute::get(fn() => $this->spec->capacity ?? null);
  }

  protected function description(): Attribute
  {
    return Attribute::get(fn() => $this->spec->description ?? []);
  }

  protected function decks(): Attribute
  {
    return Attribute::get(fn() => $this->spec->decks ?? null);
  }

  protected function displayOrder(): Attribute
  {
    return Attribute::get(fn() => $this->spec->display_order ?? null);
  }

  protected function categoryNumber(): Attribute
  {
    return Attribute::get(fn() => $this->spec->category_number ?? null);
  }

  protected function images(): Attribute
  {
    return Attribute::get(fn() => $this->spec->images ?? []);
  }

  protected function iframe(): Attribute
  {
    return Attribute::get(fn() => $this->spec->iframe ?? null);
  }

  // ==========================
  // Custom Accessors
  // ==========================

  /**
   * Accessor: Get the category title.
   */
  public function getTitleAttribute(): string
  {
    $title = $this->spec->category_name;
    if (strpos($this->spec->category_name, $this->spec->category_type) === false) {
      $title .= ' ' . $this->spec->category_type;
    }
    //$title .= ' ' . $this->spec->category_code . ' ' . $this->capacity_description;
    $title .= ' ' . $this->spec->category_code;

    return $title;
  }

  /**
   * Accessor: Get the capacity description.
   */
  protected function capacityDescription(): Attribute
  {
    return Attribute::get(function () {
      switch ($this->spec->capacity) {
        case 1:
          return 'Single';
        case 2:
          return 'Double/Twin';
        case 3:
          return 'Triple';
        case 4:
          return 'Quad';
        case 5:
          return 'Quint';
        case 6:
          return 'Sextuple';
        case 7:
          return 'Septuple';
        case 8:
          return 'Octuple';
        default:
          return 'Capacity: ' . $this->spec->capacity . ' Persons';
      }
    });
  }
}
