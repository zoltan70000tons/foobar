<?php

namespace App\Repositories;

use App\Enums\MemberShip;
use App\Models\Booking;
use App\Models\Passenger;
use App\Models\Adjustment;
use App\Models\SurvivorNumber;
use App\Models\User;

class AdjustmentsRepository
{
  protected Booking $booking;
  protected Passenger $passenger;

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

  public function getAdjustmentsBySurvivorNumber($survivorNumber): int|null
  {
      if (!$survivorNumber) {
          return null;
      }

      $userUuid = SurvivorNumber::query()
          ->where('survivor_number', $survivorNumber)
          ->value('user_id');

      $user = User::query()->where('id', $userUuid)->first();

      $memberType = strtoupper($user->membership->memberType->name);
      $result = null;

      foreach (MemberShip::cases() as $membership) {
          if($memberType == $membership->value){
              $result = Adjustment::where('code', '=', $membership->name)->first();
          }
      }

      if ($result) {
          $result = $result->id;
      }

      return $result;
  }

  public function listAdjustments() {
    return Adjustment::where('system', true)->get();
  }

  public function getSingleTicketFeeId(): int
  {
      return Adjustment::query()
          ->where('code', 'SINGLE_TICKET_FEE')
          ->value('id');
  }

  public function getPaidInFullId(): int
  {
      return Adjustment::query()
          ->where('code', 'PAID_IN_FULL')
          ->value('id');
  }

    public function getTaxAdjustmentId(): int
    {
        return Adjustment::query()
            ->where('code', 'TAX')
            ->value('id');
    }

    public function getChooseYourCabinFeeId(): int
    {
        return Adjustment::query()
            ->where('code', 'CHOOSE_YOUR_CABIN')
            ->value('id');
    }

    public function getCarbonOffsetFeeId($code): int
    {
        return Adjustment::query()
            ->where('code', $code)
            ->value('id');
    }

  public function getIdByCode(string $code): int|null
  {
      return match ($code) {
          'SINGLE_TICKET_FEE' => $this->getSingleTicketFeeId(),
          'PAID_IN_FULL' => $this->getPaidInFullId(),
          'TAX' => $this->getTaxAdjustmentId(),
          'CHOOSE_YOUR_CABIN' => $this->getChooseYourCabinFeeId(),
          'CARBON_OFFSET_I', 'CARBON_OFFSET_B', 'CARBON_OFFSET_S', 'CARBON_OFFSET_O' => $this->getCarbonOffsetFeeId($code),
          default => null,
      };
  }
}
