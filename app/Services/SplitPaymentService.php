<?php

namespace App\Services;

use App\Models\Installment;
use App\Models\Passenger;
use App\Models\Payment;
use App\Models\PaymentTransfer;
use App\Repositories\PaymentRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Log;
use Validator;

class SplitPaymentService
{
    protected $paymentRepository;

    public function __construct(PaymentRepository $paymentRepository)
    {
        $this->paymentRepository = $paymentRepository;
    }

    public function registerSplitPayment($payload, $transactionId)
    {
        $user = Auth::user();

        $payment = new Payment();
        $payment->passenger_id = $payload["passengerId"];
        $payment->BIP_ID = $transactionId;
        $payment->type = "PAYMENT";
        $payment->transaction_date = Carbon::now();
        $payment->amount = $payload["amount"];
        $payment->source = "MANUAL";
        $payment->notes = "Split payment added by " . $user->username;
        $payment->splitAmount = true;

        $payment->save();
    }
}
