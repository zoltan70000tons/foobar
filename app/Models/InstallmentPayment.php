<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstallmentPayment extends Model
{
    use HasFactory;

    protected $table = 'installment_payment'; 

    protected $fillable = [
        'installment_id',
        'payment_id',
        'amount_paid',
        'status'
    ];

    public function installment()
    {
        return $this->belongsTo(Installment::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
