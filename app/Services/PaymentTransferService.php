<?php

namespace App\Services;

use App\Enums\GlobalLog\LogActionBooking;
use App\Models\Payment;
use App\Models\PaymentTransfer;
use App\Repositories\PaymentRepository;
use App\Support\GlobalLogger;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PaymentTransferService {
    protected $paymentRepository;

    public function __construct(PaymentRepository $paymentRepository) {
        $this->paymentRepository = $paymentRepository;
    }

    public function transferPayment($payload) {
        $user = Auth::user();

        $fromPayment = new Payment();
        $fromPayment->passenger_id = $payload['passenger_id'];
        $fromPayment->BIP_ID = Str::uuid()->toString();
        $fromPayment->type = 'TRANSFER';
        $fromPayment->transaction_date = Carbon::now();
        $fromPayment->amount = $payload['amount'] * -1;
        $fromPayment->source = 'MANUAL';
        $fromPayment->notes =
            'Payment transferred to passenger ' . $payload['passenger_order'] . ' by ' . $user->username;

        $toPayment = new Payment();
        $toPayment->passenger_id = $payload['transfer_to_passenger'];
        $toPayment->BIP_ID = Str::uuid()->toString();
        $toPayment->type = 'TRANSFER';
        $toPayment->transaction_date = Carbon::now();
        $toPayment->amount = $payload['amount'];
        $toPayment->source = 'MANUAL';
        $toPayment->notes =
            'Payment transferred from passenger ' . $payload['passenger_order'] . ' by ' . $user->username;

        $fromPayment->save();
        $toPayment->save();

        PaymentTransfer::create([
            'payment_id_from' => $fromPayment->id,
            'payment_id_to' => $toPayment->id,
            'passenger_id_from' => $payload['passenger_id'],
            'passenger_id_to' => $payload['transfer_to_passenger'],
        ]);

        $passengerFrom = $fromPayment->passenger;
        $booking = $passengerFrom->booking;

        GlobalLogger::log(
            LogActionBooking::TRANSFER_PAYMENT_CREATED,
            'booking',
            $booking->id,
            'Transfer created between passengers',
            [
                'after' => [
                    'from_payment' => [
                        'payment_id' => $fromPayment->id,
                        'passenger_id' => $fromPayment->passenger_id,
                        'amount' => $fromPayment->amount,
                        'notes' => $fromPayment->notes,
                        'transaction_id' => $fromPayment->BIP_ID,
                    ],
                    'to_payment' => [
                        'payment_id' => $toPayment->id,
                        'passenger_id' => $toPayment->passenger_id,
                        'amount' => $toPayment->amount,
                        'notes' => $toPayment->notes,
                        'transaction_id' => $toPayment->BIP_ID,
                    ],
                    'booking_id' => $booking->id,
                    'booking_request_id' => $booking->booking_request_id,
                ],
            ],
        );
    }
}
