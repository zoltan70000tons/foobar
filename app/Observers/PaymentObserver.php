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

        if ($payment->type === "REFUND") {
            $description = 'Refund created';
            $action = LogActionBooking::REFUND_CREATED;
        } else {
            $description = 'Payment created';
            $action = LogActionBooking::PAYMENT_CREATED;

            if ($payment->splitAmount) {
                $description = 'Split payment created for the ' . $this->passengerOrderToString($payment->passenger->passenger_order) .
                    ' passenger.';
                $action = LogActionBooking::SPLIT_PAYMENT_CREATED;
            }

            if ($payment->type === 'TRANSFER') {
                return;
            }
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

        if ($payment->type === "REFUND") {
            $description = 'Refund deleted';
            $action = LogActionBooking::REFUND_DELETED;
        } else {
            $description = 'Payment deleted';
            $action = LogActionBooking::PAYMENT_DELETED;

            if ($payment->splitAmount) {
                $description = 'Split payment deleted';
                $action = LogActionBooking::SPLIT_PAYMENT_DELETED;
            }

            if ($payment->type === 'TRANSFER') {
                return;
            }
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

    private function passengerOrderToString(int $passengerOrder): string
    {
        return match ($passengerOrder) {
            1 => 'Lead',
            2 => '2nd',
            3 => '3rd',
            4, 5, 6, 7, 8 => "{$passengerOrder}th",
            default => "",
        };
    }
}
