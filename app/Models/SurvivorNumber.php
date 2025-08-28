<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $survivor_number
 */
class SurvivorNumber extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'survivor_number'];

    // Relationship with User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // public function customer()
    // {
    //     return $this->belongsTo(Customer::class);
    // }
}
