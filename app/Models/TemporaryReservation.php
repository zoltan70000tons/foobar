<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemporaryReservation extends Model
{
    use HasFactory;

    protected $fillable = ['cabin_id', 'user_id', 'expires_at'];

}
