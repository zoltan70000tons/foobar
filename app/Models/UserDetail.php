<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $first_name
 * @property string $last_name
 * @property string|null $middle_name
 * @property string|null $dob
 * @property string|null $citizenship
 * @property string|null $phone
 * @property string|null $emergency_c_name
 * @property string|null $emergency_c_phone
 * @property string|null $language
 * @property string|null $gender
 * @property string $user_id
 * @property int $id
 */
class UserDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'gender',
        'first_name',
        'dob',
        'middle_name',
        'last_name',
        'citizenship',
        'phone',
        'avatar',
        'emergency_c_name',
        'emergency_c_phone',
        'language'
    ];
    protected $appends = ['full_name', 'short_name'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
    }
    public function getShortNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
