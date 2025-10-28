<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Comment extends Model
{
  use HasFactory;
  protected $table = "booking_comments";

  protected $fillable = [
    'id',
    'booking_id',
    'user_id',
    'comment',
    'created_at',
    'updated_at',

  ];
  
  public function booking()
  {
    return $this->belongsTo(Booking::class, 'booking_id');
  }

  public function user()
  {
    return $this->belongsTo(User::class, 'user_id')->select(['id','username']); 
  }

}
