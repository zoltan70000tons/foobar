<?php

namespace App\Repositories;

use App\Models\Adjustment;
use App\Models\Booking;
use App\Models\Fee;
use App\Models\PassengerDiscount;
use App\Models\Payment;
use App\Models\Passenger;
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
      // Load all required models and relations
      $booking = Booking::with(['cabin.category', 'adjustments'])->findOrFail($bookingId);
      $passenger = Passenger::with(['fees', 'discounts'])->findOrFail($passengerId);

      $cabinPrice = $booking->cabin->category->price ?? 0;

      $totalDiscount = 0;
      $totalAddon = 0;
      $totalFees = $passenger->fees->sum('amount');

      // Process booking-level adjustments (global discounts or addons)
      foreach ($booking->adjustments as $adjustment) {
        $adjustmentValue = $adjustment->value;

        // Convert percentage adjustments into actual amounts
        if ($adjustment->operation === 'PERCENTAGE') {
          $adjustmentValue = ($cabinPrice * $adjustmentValue) / 100;
        }

        // Accumulate discounts and addons separately
        if ($adjustment->type === 'DISCOUNT') {
          $totalDiscount += $adjustmentValue;
        } elseif ($adjustment->type === 'ADDON') {
          $totalAddon += $adjustmentValue;
        }
      }

      // Process passenger-specific discounts
      foreach ($passenger->discounts as $discount) {
        $discountValue = $discount->amount;

        // Convert percentage-based discounts to actual amounts
        if ($discount->operation === 'PERCENTAGE') {
          $discountValue = ($cabinPrice * $discount->amount) / 100;
        }

        $totalDiscount += $discountValue;
      }

      // Final calculation:
      // Start with base price, subtract total discounts, add addons and fees
      $allocatedCost = max(0, $cabinPrice - $totalDiscount + $totalAddon + $totalFees);

      // Update the passenger with the recalculated allocated cost
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

      $payments = Payment::where('passenger_id', $passengerId)
        ->selectRaw("
                    SUM(CASE WHEN type = 'PAYMENT' THEN amount ELSE 0 END) -
                    SUM(CASE WHEN type = 'REFUND' THEN amount ELSE 0 END) AS balance
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
