<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use App\Models\Payment;

class PaymentController extends Controller {
    protected PaymentService $paymentService;
    public function __construct(PaymentService $paymentService) {
        $this->paymentService = $paymentService;
    }

    public function processPayment(Request $request) {
        $validated = $request->validate([
            'amount' => 'required|numeric',
            'bip_id' => 'required|string',
            'type' => 'required|in:PAYMENT,REFUND',
            'passenger_id' => 'required|exists:passengers,id',
        ]);
        $data = $validated;
        $data['source'] = 'SYSTEM';

        $this->paymentService->processPayment($data);
        return response()->json(
            [
                'message' => 'Payment processed successfully',
                'payment_id' => $payment->id,
            ],
            200,
        );
    }
}
