<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Membership extends Model
{
  use HasFactory;

  protected $fillable = ['user_id', 'membership_id'];
  public function memberType()
  {
      return $this->hasOne(MembershipType::class, 'id', 'membership_id');
  }
  
}
