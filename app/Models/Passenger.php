<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Passenger extends Model
{
    use HasApiTokens, HasFactory;

    protected $table = 'passengers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'booking_id',
        'confirmed_booking_email',
        'lead_passenger',
        'survivor_number',
        'payment_method',
        'gender',
        'first_name',
        'middle_name',
        'last_name',
        'dob',
        'citizenship',
        'address_first',
        'address_second',
        'city',
        'state',
        'postal_code',
        'country',
        'email',
        'phone',
        'emergency_c_name',
        'emergency_c_phone',
        'special_request',
        'hear_about',
        'newsletter',
        'travel_info',
        'terms_n_cons',
        'cabin_conf_accp',
        'single_t_agreement',
        'passenger_allocated_cost',
        'passenger_balance',
        'was_on_board',
    ];

    /**
     * Relationship: A passenger belongs to a booking.
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
