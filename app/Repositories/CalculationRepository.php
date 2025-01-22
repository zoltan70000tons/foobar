<?php

namespace App\Repositories;

use App\Models\Adjustment;
use App\Models\Booking;
use App\Models\Fee;
use App\Models\Payment;
use Log;

class CalculationRepository
{

    protected PassengerRepository $passengerRepository;
    public function __construct(PassengerRepository $passengerRepository)
    {
        $this->passengerRepository = $passengerRepository;
    }

    public function recalculateAllocatedCost($passengerId, $bookingId, $eventId)
    {
        try {

            $passenger = $this->passengerRepository->find($eventId, $passengerId, $bookingId);
            $booking = Booking::find($bookingId);
            $cabinCategoryPrice = $booking->cabin->category->price ?? 0;
            $passengerFees = Fee::where('passenger_id', $passengerId)->sum('amount');
            $adjustments = Adjustment::join('booking_has_adjustments', 'adjustments.id', '=', 'booking_has_adjustments.adjustment_id')
                ->where('booking_has_adjustments.booking_id', $booking->id)
                ->get();

            $adjustmentTotal = 0;

            foreach ($adjustments as $adjustment) {
                if ($adjustment->operation === 'FIXED') {
                    $adjustmentTotal += $adjustment->value;
                } elseif ($adjustment->operation === 'PERCENTAGE') {
                    $adjustmentTotal += ($cabinCategoryPrice * $adjustment->value) / 100;
                }
            }
            $allocatedCost = $cabinCategoryPrice + $passengerFees + $adjustmentTotal;

            // Update the passenger's allocated cost
            $passenger->update(['passenger_allocated_cost' => $allocatedCost]);

            return $allocatedCost;
        } catch (\Exception $e) {
            Log::error("Error recalculating allocated cost for passenger {$passengerId}: {$e->getMessage()}");
            return false;
        }
    }

    public function recalculateBalance($passengerId, $bookingId, $eventId)
    {
        try {
            $passenger = $this->passengerRepository->find($eventId, $passengerId, $bookingId);
            $booking = Booking::find($bookingId);
            $cabinCategoryPrice = $booking->cabin->category->price ?? 0;
    
            $payments = Payment::where('passenger_id', $passengerId)
                ->selectRaw("
                    SUM(CASE WHEN type = 'PAYMENT' THEN amount ELSE 0 END) -
                    SUM(CASE WHEN type = 'REFOUND' THEN amount ELSE 0 END) AS balance
                ")
                ->first();
            $balance = $payments->balance ?? 0;
            $passenger->update(['passenger_balance' => $balance]);
            return $balance;
        } catch (\Exception $e) {
            Log::error("Error recalculating allocated cost for passenger {$passengerId}: {$e->getMessage()}");
            return false;
        }
    }
    
}
