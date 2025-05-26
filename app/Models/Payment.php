<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class Payment extends Model
{
  use HasApiTokens, HasFactory, SoftDeletes;

  protected $table = 'payments';

  protected $fillable = [
    'passenger_id',
    'BIP_ID',
    'type',
    'transaction_date',
    'amount',
    'notes',
    'source',
    'splitAmount',
  ];

  protected static function boot()
  {
    parent::boot();
    static::created(function ($payment) {
     
    });
  }
  
  public function passenger()
  {
    return $this->belongsTo(Passenger::class, 'passenger_id');
  }

    public function paymentTransferFrom()
    {
        return $this->hasOne(PaymentTransfer::class, 'payment_id_from');
    }

    public function paymentTransferTo()
    {
        return $this->hasOne(PaymentTransfer::class, 'payment_id_to');
    }

    public function getMergedPaymentTransfersAttribute()
    {
        // Retrieve payment transfers: from and to, defaulting to null if not set
        $transfersFrom = $this->paymentTransferFrom;
        $transfersTo = $this->paymentTransferTo;

        // If both are null, return an empty collection
        if (is_null($transfersFrom) && is_null($transfersTo)) {
            return collect();
        }

        // If either transfer exists, return the relevant one(s)
        return collect([$transfersFrom, $transfersTo])->filter()->values(); // Filter out null values
    }
}
