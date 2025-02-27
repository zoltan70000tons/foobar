<?php

namespace App\Services;

use App\Models\Booking;

class PaymentInfoService
{

    private function formatCurrency(float $amount): string
    {
        return 'USD ' . number_format($amount, 2, '.', ',');
    }

    public function getPaymentInfo(Booking $booking): array
    {
        $cabin = $booking->cabin;
        $event = $booking->event;
        $category = $cabin->category;
        $cabinSpec = $cabin->cabinSpec;
        $cabinType = $cabin->cabinType;
        $passengers = $booking->passengers
            ->sort(function ($a, $b) {
                return [$b->lead_passenger, $a->id] <=> [$a->lead_passenger, $b->id];
            });

        $adjustments = $booking->adjustments;
        $paymentInfo = [
            'amount_to_pay' => 0,
            'paid_amount' => 0,
            'remaining_to_pay' => 0,
            'cabin_number' => $cabinSpec->cabin_number,
            'capacity' => $category->capacity,
            'price_per_person' => $category->price,
            'payment_type' => $booking->payment_plan,
            'total_fees' => 0,
            'total_addons' => 0,
            'total_discounts' => 0,
            'passengers' => [],

        ];

        foreach ($adjustments as $adjustment) {
            $amount = $adjustment->value;
            if ($adjustment->operation === 'PERCENTAGE') {
                $amount = ($amount / 100) * $paymentInfo['amount_to_pay'];
            }


            if ($adjustment->type === 'ADDON') {
                $paymentInfo['total_addons'] += $amount;
            } elseif ($adjustment->type === 'DISCOUNT') {
                $paymentInfo['total_discounts'] += $amount;
            }

        }

        foreach ($passengers as $passenger) {
            $passengerBalance = $passenger->passenger_balance;
            $paymentMethod = $passenger->payment_method;
            // Sum all amounts in the balance
            $allocatedCost = $passenger->passenger_allocated_cost;
            $fees = $passenger->fees->sum('amount');
            // Store passenger info
            $paymentInfo['passengers'][] = [
                'payment_method' => $paymentMethod,
                'balance' => $passengerBalance,
                'allocated_cost' => $allocatedCost,
                'fees' => $fees,
            ];

            // Add allocated cost to total amount_to_pay
            $paymentInfo['amount_to_pay'] += $allocatedCost;
            $paymentInfo['paid_amount'] += $passengerBalance;
            $paymentInfo['total_fees'] += $fees;
            $paymentInfo['remaining_to_pay'] = $paymentInfo['amount_to_pay'] - $paymentInfo['paid_amount'];
        }

        $paymentInfo['amount_to_pay'] = $this->formatCurrency($paymentInfo['amount_to_pay']);
        $paymentInfo['paid_amount'] = $this->formatCurrency($paymentInfo['paid_amount']);
        $paymentInfo['total_fees'] = $this->formatCurrency($paymentInfo['total_fees']);
        $paymentInfo['total_addons'] = $this->formatCurrency((float) $paymentInfo['total_addons']);
        $paymentInfo['total_discounts'] = $this->formatCurrency((float) $paymentInfo['total_discounts']);

        return $paymentInfo;
    }
}
