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

    public function registerSplitPayment($payload)
    {
        $user = Auth::user();

        $fromPayment = new Payment();
        $fromPayment->passenger_id = $payload["passengerId"];
        $fromPayment->BIP_ID = Str::uuid()->toString();
        $fromPayment->type = "PAYMENT";
        $fromPayment->transaction_date = Carbon::now();
        $fromPayment->amount = $payload["amount"];
        $fromPayment->source = "MANUAL";
        $fromPayment->notes = "Split payment added by " . $user->username;

        $fromPayment->save();
    }
}
