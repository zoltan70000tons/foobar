<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentTransfer extends Model {
    use HasFactory, SoftDeletes;

    protected $fillable = ['payment_id_from', 'payment_id_to', 'passenger_id_from', 'passenger_id_to'];

    public function paymentFrom() {
        return $this->belongsTo(Payment::class, 'payment_id_from');
    }

    public function paymentTo() {
        return $this->belongsTo(Payment::class, 'payment_id_to');
    }
}
