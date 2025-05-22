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

class PaymentTransferService
{
    protected $paymentRepository;

    public function __construct(PaymentRepository $paymentRepository)
    {
        $this->paymentRepository = $paymentRepository;
    }

    public function transferPayment($payload)
    {
        $user = Auth::user();

        $fromPayment = new Payment();
        $fromPayment->passenger_id = $payload["passenger_id"];
        $fromPayment->BIP_ID = Str::uuid()->toString();
        $fromPayment->type = "TRANSFER";
        $fromPayment->transaction_date = Carbon::now();
        $fromPayment->amount = $payload["amount"] * -1;
        $fromPayment->source = "MANUAL";
        $fromPayment->notes = "Payment transferred to passenger " . $payload["transfer_to_passenger"] . " by " . $user->username;

        $toPayment = new Payment();
        $toPayment->passenger_id = $payload["transfer_to_passenger"];
        $toPayment->BIP_ID = Str::uuid()->toString();
        $toPayment->type = "TRANSFER";
        $toPayment->transaction_date = Carbon::now();
        $toPayment->amount = $payload["amount"];
        $toPayment->source = "MANUAL";
        $toPayment->notes = "Payment transferred from passenger " . $payload["passenger_id"] . " by " . $user->username;

        $fromPayment->save();
        $toPayment->save();

        PaymentTransfer::create([
            "payment_id_from" => $fromPayment->id,
            "payment_id_to" => $toPayment->id,
            "passenger_id_from" => $payload["passenger_id"],
            "passenger_id_to" => $payload["transfer_to_passenger"],
        ]);
    }
}
