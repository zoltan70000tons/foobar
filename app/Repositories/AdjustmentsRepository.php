<?php

namespace App\Repositories;

use App\Enums\MemberShip;
use App\Models\Booking;
use App\Models\Passenger;
use App\Models\Adjustment;

class AdjustmentsRepository
{
  protected $booking;
  protected $passenger;

  public function __construct(Passenger $passenger, Booking $booking)
  {
    $this->booking = $booking;
    $this->passenger = $passenger;
  }
  /**
   * Attach adjustments to a booking.
   *
   * @param array $adjustmentIds Array of adjustment IDs to attach.
   * @param Booking $booking The booking instance.
   * @return bool
   */
  public function attachAdjustments(array $adjustmentIds, Booking $booking): bool
  {
    try {
      $booking->adjustments()->sync($adjustmentIds);

      return true;
    } catch (\Exception $e) {
      \Log::error("Failed to attach adjustments: " . $e->getMessage());
      return false;
    }
  }

  public function getAdjustments($user, $cabin, $cabinOffset = false, $singleTicket = false){
       $memberType = strtoupper($user->membership->memberType->name);
       $price = $cabin->category->price;
       foreach (MemberShip::cases() as $membership) {
        if($memberType == $membership->value){
          $result = Adjustment::where('code', '=', $membership->name)->get();
        }
        
      }
  }

  public function listAdjustments() {
    return Adjustment::all();
  }
}
