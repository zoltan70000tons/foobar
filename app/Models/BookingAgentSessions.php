<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingAgentSessions extends Model
{
    protected $table = 'booking_agent_sessions';
    
    protected $fillable = ['booking_id', 'agent_id', 'time'];
    public $timestamps = false;
    
    public function booking()
    {
      return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function agent()
    {
      return $this->belongsTo(User::class, 'agent_id');
    }
}
