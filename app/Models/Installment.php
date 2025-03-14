<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Installment extends Model
{
  protected $table = 'installments';

  protected $fillable = ['passenger_id', 'due_date', 'type','fee_id'];


  public function payments()
    {
        return $this->belongsToMany(Payment::class, 'installment_payment')
            ->withPivot('amount_paid', 'status')
            ->withTimestamps();
    }

    public function scopeUnpaid($query)
    {
        return $query->whereDoesntHave('payments', function ($q) {
            $q->where('installment_payment.status', 'PAID');
        });
    }

  public function fee(){
    return $this->belongsTo(Fee::class, 'fee_id');
  }


}
