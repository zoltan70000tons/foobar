<?php

// app/Observers/PaymentObserver.php
namespace App\Observers;

use App\Enums\GlobalLog\LogActionBooking;
use App\Models\Payment;
use App\Support\GlobalLogger;

class PaymentObserver
{
    public function created(Payment $payment): void
    {
        $booking = $payment->passenger->booking;
        $description = 'Payment created';
        $action = LogActionBooking::PAYMENT_CREATED;

        if ($payment->splitAmount) {
            $description = 'Split payment created';
            $action = LogActionBooking::SPLIT_PAYMENT_CREATED;
        }

        if ($payment->type === 'TRANSFER') {
            $description = 'Transfer payment created';
            $action = LogActionBooking::TRANSFER_PAYMENT_CREATED;
        }

        GlobalLogger::log(
            $action,
            'booking',
            $booking->id,
            $description,
            [
                'after' => [
                    'payment_id' => $payment->id,
                    'passenger_id' => $payment->passenger_id,
                    'amount' => $payment->amount,
                    'type' => $payment->type,
                    'notes' => $payment->notes,
                    'transaction_date' => $payment->transaction_date,
                    'transaction_id' => $payment->BIP_ID,
                    'booking_id' => $booking->id,
                    'booking_request_id' => $booking->booking_request_id,
                ],
            ]
        );
    }

    public function deleted(Payment $payment): void
    {
        $booking = $payment->passenger->booking;
        $description = 'Payment deleted';
        $action = LogActionBooking::PAYMENT_DELETED;

        if ($payment->splitAmount) {
            $description = 'Split payment deleted';
            $action = LogActionBooking::SPLIT_PAYMENT_DELETED;
        }

        if ($payment->type === 'TRANSFER') {
            $description = 'Transfer payment deleted';
            $action = LogActionBooking::TRANSFER_PAYMENT_DELETED;
        }

        GlobalLogger::log(
            $action,
            'booking',
            $booking->id,
            $description,
            [
                'before' => [
                    'payment_id' => $payment->id,
                    'passenger_id' => $payment->passenger_id,
                    'amount' => $payment->amount,
                    'type' => $payment->type,
                    'notes' => $payment->notes,
                    'transaction_date' => $payment->transaction_date,
                    'transaction_id' => $payment->BIP_ID,
                    'booking_id' => $booking->id,
                    'booking_request_id' => $booking->booking_request_id,
                ],
            ]
        );
    }
}
