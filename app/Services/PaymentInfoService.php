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
        $grandTotal = $passengers->sum('passenger_allocated_cost');
        $paymentInfo = [
            'amount_to_pay' => 0,
            'grand_total' => $this->formatCurrency($grandTotal),
            'paid_amount' => 0,
            'remaining_to_pay' => 0,
            'cabin_number' => $cabinSpec->cabin_number,
            'capacity' => $category->capacity,
            'price_per_person' => $category->price,
            'payment_type' => $booking->payment_plan,
            'total_fees' => 0,
            'total_addons' => 0,
            'total_discounts' => 0,
            'discounts_detail' => [],
            'net_ticket_price' => 0,
            'passengers' => [],

        ];

        $typeMap = [
            'ADDON' => ['key' => 'total_addons', 'detail' => 'addons_detail', 'symbol' => '+'],
            'DISCOUNT' => ['key' => 'total_discounts', 'detail' => 'discount_detail', 'symbol' => '-'],
        ];

        foreach ($adjustments as $adjustment) {
            $amount = $adjustment->value;
            if ($adjustment->operation === 'PERCENTAGE') {
                $amount = ($amount / 100) * $paymentInfo['amount_to_pay'];
            }
            
            if (isset($typeMap[$adjustment->type])) {
                $key = $typeMap[$adjustment->type]['key'];
                $detail = $typeMap[$adjustment->type]['detail'];
                $symbol = $typeMap[$adjustment->type]['symbol'];
                $paymentInfo[$key] += $amount;
                $paymentInfo[$detail][] = [
                    'type' => $symbol,
                    'amount' => $amount,
                    'formated_amount' => formatCurrency($amount),
                    'percentaje' => $adjustment->operation === 'PERCENTAJE',
                    'code' => $adjustment->code
                ];
            }
            
        }

        foreach ($passengers as $passenger) {
            $bookingAddons = $paymentInfo['addons_detail'];
            $bookingDiscounts = $paymentInfo['discount_detail'];
            $passengerFees = $passenger->fees;
            foreach($passengerFees as $fee){
                $bookingAddons[] = [
                    'type' => '+',
                    'amount' => $fee->amount,
                    'formated_amount' => formatCurrency($fee->amount),
                    'percentaje' => false,
                    'code' => $fee->type
                ];
            }

            $totalAmount = array_reduce($bookingAddons, function ($carry, $item) {
                return $carry + $item['amount'];
            }, 0);

            $totalDiscounts = array_reduce($bookingDiscounts, function ($carry, $item) {
                return $carry + $item['amount'];
            }, 0);
            $passengerBalance = $passenger->passenger_balance;
            $paymentMethod = $passenger->payment_method;
            if($paymentMethod == 'CREDIT_CARD'){
                $paymentMethod = 'Credit Card*';
            }else{
                $paymentMethod = 'Pay In Full';
            }
            // Sum all amounts in the balance
            $allocatedCost = $passenger->passenger_allocated_cost;
            $fees = $passenger->fees->sum('amount');
            $netTicketPrice = $category->price - $totalDiscounts;
            $totalTicketPrice = $netTicketPrice + $totalAmount;
            // Store passenger info
            $paymentInfo['passengers'][] = [
                'passenger' => $passenger,
                'payment_method' => $paymentMethod,
                'balance' => $passengerBalance,
                'formated_balance' =>formatCurrency($passengerBalance),
                'allocated_cost' => $allocatedCost,
                'formated_allocated_cost' => formatCurrency($allocatedCost),
                'fees' => $fees,
                'passenger_discounts' => $bookingDiscounts,
                'passenger_addons' => $bookingAddons,
                'total_addons' => $totalAmount,
                'total_discounts' =>$totalDiscounts,
                'net_ticket_price' => $netTicketPrice,
                'formated_total_addons' => formatCurrency($totalAmount),
                'total_ticket_price' => formatCurrency($totalTicketPrice)

            ];
            
            $paymentInfo['net_ticket_price'] = $paymentInfo['price_per_person'] - $paymentInfo['total_discounts'];

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
 