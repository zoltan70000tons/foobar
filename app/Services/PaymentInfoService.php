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
        $category = $cabin->category;
        $cabinSpec = $cabin->cabinSpec;
        $cabinType = $cabin->cabinType;
        $passengers = $booking->passengers;
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
            'addons_detail' => [],
            'formatted_amount_to_pay' => null,
            'formatted_paid_amount' => null,
            'formatted_remaining_to_pay' => null,
            'formatted_price_per_person' => null,
            'formatted_total_fees' => null,
            'formatted_total_addons' => null,
            'formatted_total_discounts' => null,
            'formatted_net_ticket_price' => null
        ];

        $typeMap = [
            'ADDON' => ['key' => 'total_addons', 'detail' => 'addons_detail', 'symbol' => '+'],
            'DISCOUNT' => ['key' => 'total_discounts', 'detail' => 'discounts_detail', 'symbol' => '-'],
        ];

        foreach ($adjustments as $adjustment) {
            $amount = $this->calculateAdjustmentAmount($adjustment, $category->price);

            if (isset($typeMap[$adjustment->type])) {
                $key = $typeMap[$adjustment->type]['key'];
                $detail = $typeMap[$adjustment->type]['detail'];
                $symbol = $typeMap[$adjustment->type]['symbol'];

                $paymentInfo[$key] += $amount;
                $paymentInfo[$detail][] = [
                    'type' => $symbol,
                    'amount' => $amount,
                    'formatted_amount' => $this->formatCurrency($amount),
                    'percentage' => $adjustment->operation === 'PERCENTAGE',
                    'code' => $adjustment->code
                ];
            }
        }

        $paymentInfo['passengers'] = $passengers->map(
            fn($passenger) =>
            $this->mapPassengerInfo($passenger, $category->price, $paymentInfo)
        )->all();

        // Calculate final totals
        $paymentInfo['net_ticket_price'] = $paymentInfo['price_per_person'] - $paymentInfo['total_discounts'];
        $paymentInfo['remaining_to_pay'] = $paymentInfo['amount_to_pay'] - $paymentInfo['paid_amount'];

        // Format money
        foreach (['amount_to_pay', 'paid_amount', 'total_fees', 'remaining_to_pay', 'price_per_person', 'total_addons', 'total_discounts', 'net_ticket_price'] as $key) {
            $paymentInfo['formatted_' . $key] = $this->formatCurrency((float) $paymentInfo[$key]);
        }

        return $paymentInfo;
    }

    private function calculateAdjustmentAmount($adjustment, $price)
    {
        return $adjustment->operation === 'PERCENTAGE'
            ? ($adjustment->value / 100) * $price
            : $adjustment->value;
    }

    private function mapPassengerInfo($passenger, $basePrice, &$paymentInfo)
    {
        $bookingAddons = $paymentInfo['addons_detail'] ?? [];
        $bookingDiscounts = $paymentInfo['discounts_detail'] ?? [];

        foreach ($passenger->fees as $fee) {
            $bookingAddons[] = [
                'type' => '+',
                'amount' => $fee->amount,
                'formatted_amount' => $this->formatCurrency($fee->amount),
                'percentage' => false,
                'code' => $fee->type
            ];
        }

        $totalAddons = array_sum(array_column($bookingAddons, 'amount'));
        $totalDiscounts = array_sum(array_column($bookingDiscounts, 'amount'));
        $totalDiscountsPercentage = $basePrice > 0 ? round(($totalDiscounts / $basePrice) * 100, 2) : 0;
        $allocatedCost = $passenger->passenger_allocated_cost;
        $fees = $passenger->fees->sum('amount');
        $netTicketPrice = $basePrice - $totalDiscounts;
        $totalTicketPrice = $netTicketPrice + $totalAddons;

        $paymentMethod = $passenger->payment_method === 'CREDIT_CARD' ? 'Credit Card*' : 'Pay In Full';

        // update totals
        $paymentInfo['amount_to_pay'] += $allocatedCost;
        $paymentInfo['paid_amount'] += $passenger->passenger_balance;
        $paymentInfo['total_fees'] += $fees;

        return [
            'passenger' => $passenger,
            'payments' => $this->getPassengerInstallmentsInfo($passenger->id),
            'payment_method' => $paymentMethod,
            'total_ticket_price' => $this->formatCurrency($totalTicketPrice),
            'balance' => $passenger->passenger_balance,
            'allocated_cost' => $allocatedCost,
            'fees' => $fees,
            'passenger_discounts' => $bookingDiscounts,
            'passenger_addons' => $bookingAddons,
            'total_addons' => $totalAddons,
            'total_discounts' => $totalDiscounts,
            'net_ticket_price' => $netTicketPrice,
            'total_discounts_percentage' => $totalDiscountsPercentage,

            //format values
            'formated_ticket_price' => $this->formatCurrency($netTicketPrice),
            'formatted_balance' => $this->formatCurrency($passenger->passenger_balance),
            'formatted_allocated_cost' => $this->formatCurrency($allocatedCost),
            'formatted_total_discounts' => $this->formatCurrency($totalDiscounts),
            'formatted_total_addons' => $this->formatCurrency($totalAddons),
            'formatted_net_ticket_price' => $this->formatCurrency($netTicketPrice)

        ];
    }



    public function getPassengerInstallmentsInfo($passengerId)
    {
        $passenger = Passenger::findOrFail($passengerId);
        if ($passenger->booking->payment_plan !== 'INSTALLMENTS') {
            return [
                'due_date' => 0,
                'amount' => 0,
                'paid' => 0,
                'overdue' => false,
                'amount_paid' => 0,
                'paid_at' => 0,
                'payment_type' =>  null,
                'type' => 0
            ];
        }
        $data = $passenger->getPaymentInfoAttribute();
        $installments = $data['installments'];
        return $installments->map(function ($installment) {
            return [
                'due_date' => $installment['due_date'],
                'amount' => $installment['amount'],
                'paid' => $installment['total_paid'],
                'overdue' => $installment['status'] == 'OVERDUE' ? true : false,
                'amount_paid' => $installment['total_paid'],
                'paid_at' => null,
                'payment_type' =>  null,
                'type' => $installment['type']
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
                $passenger->update(['passenger_allocated_cost' => $totalPassenger]);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
