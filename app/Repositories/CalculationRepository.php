<?php

namespace App\Repositories;

use App\Models\Adjustment;
use App\Models\Booking;
use App\Models\Fee;
use App\Models\PassengerDiscount;
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
      $cabinPrice = $booking->cabin->category->price ?? 0;
      $passengerFees = Fee::where('passenger_id', $passengerId)->sum('amount');
      $passengerDiscounts = PassengerDiscount::where('passenger_id', $passengerId)->sum('amount');

      // Fetch all adjustments
      $adjustments = Adjustment::join('booking_has_adjustments', 'adjustments.id', '=', 'booking_has_adjustments.adjustment_id')
        ->where('booking_has_adjustments.booking_id', $booking->id)
        ->get();

      $sumOfFixedDiscounts = 0;
      $sumOfPercentagesDiscounts = 0;
      $sumOfFixedAddons = 0;
      $sumOfPercentageAddons = 0;

      foreach ($adjustments as $adjustment) {
        if ($adjustment->operation === 'FIXED' && $adjustment->type === 'DISCOUNT') {
          $sumOfFixedDiscounts += $adjustment->value;
        } elseif ($adjustment->operation === 'PERCENTAGE' && $adjustment->type === 'DISCOUNT') {
          $sumOfPercentagesDiscounts += $adjustment->value;
        } elseif ($adjustment->operation === 'FIXED' && $adjustment->type === 'ADDON') {
          $sumOfFixedAddons += $adjustment->value;
        } elseif ($adjustment->operation === 'PERCENTAGE' && $adjustment->type === 'ADDON') {
          $sumOfPercentageAddons += $adjustment->value;
        }
      }

      $sumOfFixedPassengerDiscount = 0;
      $sumOfPercentagesPassengerDiscount = 0;

      foreach ($passengerDiscounts as $discount) {
        if ($discount->operation === 'FIXED') {
          $sumOfFixedPassengerDiscount += $discount->amount;
        } elseif ($discount->operation === 'PERCENTAGE') {
          $sumOfPercentagesPassengerDiscount += $discount->amount;
        }
      }

      //adding manual discount for passenger
      $sumOfPercentagesDiscounts += $sumOfPercentagesPassengerDiscount;
      $sumOfFixedDiscounts += $sumOfFixedPassengerDiscount;

      
      // Cap discount percentage at 100%
      $validatedDiscountPercentage = min($sumOfPercentagesDiscounts, 100) / 100;

      // Apply discount first, then fixed discount
      $discountedPrice = max(0, ($cabinPrice * (1 - $validatedDiscountPercentage)) - $sumOfFixedDiscounts);

      // Apply percentage-based addons
      $addonsPercentageValue = ($cabinPrice * ($sumOfPercentageAddons / 100));
      $totalAddons = $addonsPercentageValue + $sumOfFixedAddons;

      // Final total allocated cost
      $allocatedCost = max(0, $discountedPrice + $passengerFees + $totalAddons);

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
