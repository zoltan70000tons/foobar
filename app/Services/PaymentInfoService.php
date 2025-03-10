<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Passenger;
use Carbon\Carbon;

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
            'cabinType' => $cabinType,
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
            'addons_detail' => []

        ];

        $typeMap = [
            'ADDON' => ['key' => 'total_addons', 'detail' => 'addons_detail', 'symbol' => '+'],
            'DISCOUNT' => ['key' => 'total_discounts', 'detail' => 'discount_detail', 'symbol' => '-'],
        ];


        foreach ($adjustments as $adjustment) {
            $amount = $adjustment->value;
            if ($adjustment->operation === 'PERCENTAGE') {
                $amount = ($amount / 100) * $category->price;
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
                    'percentaje' => $adjustment->operation === 'PERCENTAGE',
                    'code' => $adjustment->code
                ];
            }
        }

        foreach ($passengers as $passenger) {
            $bookingAddons = $paymentInfo['addons_detail'] ?? [];
            $bookingDiscounts = $paymentInfo['discount_detail'] ?? [];
            $passengerFees = $passenger->fees;
            foreach ($passengerFees as $fee) {
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
            if ($paymentMethod == 'CREDIT_CARD') {
                $paymentMethod = 'Credit Card*';
            } else {
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
                'payments' => $this->getPassengerInstallmentsInfo($passenger->id),
                'payment_method' => $paymentMethod,
                'balance' => $passengerBalance,
                'formated_balance' => formatCurrency($passengerBalance),
                'allocated_cost' => $allocatedCost,
                'formated_allocated_cost' => formatCurrency($allocatedCost),
                'fees' => $fees,
                'passenger_discounts' => $bookingDiscounts,
                'passenger_addons' => $bookingAddons,
                'total_addons' => $totalAmount,
                'total_discounts' => $totalDiscounts,
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
        //dd($paymentInfo);
        return $paymentInfo;
    }

    public function getPassengerInstallmentsInfo($passengerId)
    {
        $passenger = Passenger::with(['booking', 'installments', 'payments'])->findOrFail($passengerId);

        if ($passenger->booking->payment_plan !== 'INSTALLMENTS') {
            return [];
        }

        $payments = $passenger->payments()->orderBy('transaction_date')->get()->values();
        $usedPayments = collect();

        return $passenger->installments->map(function ($installment) use ($payments, &$usedPayments) {
            $payment = $payments->first(function ($payment) use ($installment, $usedPayments) {
                return !$usedPayments->contains($payment->id) && Carbon::parse($payment->transaction_date)->lte($installment->due_date);
            });
            if ($payment) {
                $usedPayments->push($payment->id);
            }

            return [
                'due_date' => $installment->due_date,
                'paid' => !is_null($payment),
                'overdue' => is_null($payment) && Carbon::parse($installment->due_date)->isPast(),
                'amount_paid' => $payment ? $payment->amount : 0.00,
                'paid_at' => $payment ? $payment->transaction_date : null,
                'payment_type' => $payment ? $payment->type : null
            ];
        });
    }


    public function syncAllocatedCost(Booking $booking)
    {
        try {
            $cabin = $booking->cabin;
            $category = $cabin->category;

            // Initialize total cost with the base price of the cabin category
            $total = $category->price;

            // Variables to accumulate different types of adjustments
            $totalDiscount = 0;
            $totalAddon = 0;
            $totalFees = 0;

            // Process adjustments (DISCOUNT and ADDON)
            foreach ($booking->adjustments as $adjustment) {
                $adjustmentValue = $adjustment->value;

                // Convert percentage-based adjustments to absolute values
                if ($adjustment->operation === 'PERCENTAGE') {
                    $adjustmentValue = ($total * $adjustmentValue) / 100;
                }

                // Accumulate DISCOUNT and ADDON separately
                if ($adjustment->type === 'DISCOUNT') {
                    $totalDiscount += $adjustmentValue; // Discounts are subtracted later
                } elseif ($adjustment->type === 'ADDON') {
                    $totalAddon += $adjustmentValue; // Addons are added after discounts
                }
            }

            // Apply all discounts first (subtract from total)
            $total -= $totalDiscount;

            // Then, apply all addons (add to total)
            $total += $totalAddon;

            foreach ($booking->passengers as $passenger) {
                $totalPassenger = $total;
                // Process passenger fees (these are always added to the total)
                foreach ($passenger->fees as $fee) {
                    $totalFees += $fee->amount;
                }
                // Add fees to the final total
                $totalPassenger += $totalFees;
                // Update the passenger's allocated cost in the database
                $passenger->update(['passenger_allocated_cost' => $total]);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
