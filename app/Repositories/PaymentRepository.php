<?php

namespace App\Repositories;

use App\Models\Adjustment;
use App\Models\Booking;
use App\Models\Fee;
use App\Models\Installment;
use Log;

class PaymentRepository
{
  protected $installment;
  protected PassengerRepository $passengerRepository;

  public function __construct(Installment $installment,PassengerRepository $passengerRepository)
  {
    $this->installment = $installment;
    $this->passengerRepository = $passengerRepository;
  }

  /**
   * Create installments for a passenger.
   *
   * @param int $passengerId
   * @param array $installments
   * @return void
   */
  public function createInstallments(int $passengerId, array $installments): void
  {
    foreach ($installments as $dueDate) {
      $this->installment->create([
        'passenger_id' => $passengerId,
        'due_date' => $dueDate,
      ]);
    }
  }


  public function recalculateAllocatedCost($passengerId,$bookingId,$eventId){
   try {
    return false;
    // $passenger = $this->passengerRepository->find($eventId,$passengerId,$bookingId);
    // $booking = Booking::find($bookingId);
    // $cabinCategoryPrice = $booking->cabin->category->price ?? 0;
    // $passengerFees = Fee::where('passenger_id', $passengerId)->sum('amount');
    // $adjustments = Adjustment::join('booking_has_adjustments', 'adjustments.id', '=', 'booking_has_adjustments.adjustment_id')
    //         ->where('booking_has_adjustments.booking_id', $booking->id)
    //         ->get();

    //     $adjustmentTotal = 0;

    //     foreach ($adjustments as $adjustment) {
    //         if ($adjustment->operation === 'FIXED') {
    //             $adjustmentTotal += $adjustment->value;
    //         } elseif ($adjustment->operation === 'PERCENTAGE') {
    //             $adjustmentTotal += ($cabinCategoryPrice * $adjustment->value) / 100;
    //         }
    //     }
    //     $allocatedCost = $cabinCategoryPrice + $passengerFees + $adjustmentTotal;

    //     // Update the passenger's allocated cost
    //     $passenger->update(['passenger_allocated_cost' => $allocatedCost]);

    //     return $allocatedCost;

   } catch (\Exception $e) {
      Log::error("Error recalculating allocated cost for passenger {$passengerId}: {$e->getMessage()}");
        return false;
   }

  }
}
