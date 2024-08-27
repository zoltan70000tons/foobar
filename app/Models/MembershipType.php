<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipType extends Model
{
    protected $table = 'membership_types';
    protected $fillable = ['name','name', 'stamp_image', 'booking_number_requirement', 'discount_value'];
}
