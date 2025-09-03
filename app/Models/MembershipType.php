<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MembershipType extends Model
{
  use HasFactory;

  protected $table = 'membership_types';
  protected $fillable = ['name', 'stamp_image', 'booking_number_requirement', 'discount_value'];


  /**
   * Get the customers for the membership type.
   * 
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function customers()
  {
    return $this->belongsToMany(User::class, 'memberships', 'membership_id', 'customer_id');
  }

  public function presalePeriods()
  {
      return $this->belongsToMany(PresalePeriod::class, 'membership_type_id');
  }
}
