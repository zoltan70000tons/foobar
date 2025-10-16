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
      $event = $booking->event;

      // Fetch all adjustments in one query
      $adjustments = Adjustment::whereIn('id', $adjustmentIds)->get();

      $selectedCabin = $booking->cabin;
      $context = [
        'cabin' => [
          'category_name' => $selectedCabin?->category?->name,
          'code' => $selectedCabin?->category?->code,
          'capacity' => $selectedCabin?->total_berths,
        ],
      ];

      $filtered = $adjustments->filter(function ($adjustment) use ($context, $event) {
        // Skip if restriction exists and doesn’t match
        if (method_exists($adjustment, 'shouldApply') && !$adjustment->shouldApply($context)) {
          return false;
        }

        // Skip MEMBERSHIP adjustments if event is PUBLIC
        if ($event && $event->status === 'PUBLIC' && str_starts_with($adjustment->code, 'MEMBERSHIP_')) {
          return false;
        }

        return true;
      });

      // Add special handling for CHOOSE_YOUR_CABIN
      $filtered->transform(function ($adjustment) use ($context) {
        if ($adjustment->code === 'CHOOSE_YOUR_CABIN' && $adjustment->shouldApply($context)) {
          $adjustment->value = 0.0;
        }
        return $adjustment;
      });

      // Sync the filtered adjustments
      $booking->adjustments()->sync($filtered->pluck('id')->toArray());

      return true;
    } catch (\Exception $e) {
      \Log::error('Failed to attach adjustments: ' . $e->getMessage());
      return false;
    }
  }

  // public function getAdjustments($user, $cabin, $cabinOffset = false, $singleTicket = false){
  //      $memberType = strtoupper($user->membership->memberType->name);
  //      $price = $cabin->category->price;
  //      foreach (MemberShip::cases() as $membership) {
  //       if($memberType == $membership->value){
  //         $result = Adjustment::where('code', '=', $membership->name)->get();
  //       }

  //     }
  // }

  public function getAdjustmentsBySurvivorNumber($survivorNumber): int|null
  {
    if (!$survivorNumber) {
      return null;
    }

    $userUuid = SurvivorNumber::query()->where('survivor_number', $survivorNumber)->value('user_id');

    $user = User::query()->where('id', $userUuid)->first();

    if (!$user->membership) {
      return null;
    }

    $memberType = strtoupper($user->membership->memberType->name);
    $result = null;

    foreach (MemberShip::cases() as $membership) {
      if ($memberType == $membership->value) {
        $result = Adjustment::where('code', '=', $membership->name)->first();
      }
    }

    if ($result) {
      $result = $result->id;
    }

    return $result;
  }

  public function listAdjustments()
  {
    //return Adjustment::where('system', true)->get();
    return Adjustment::query()->orderBy('system', 'DESC')->get(); //According to the Manual Adjustments - Adjustments
    // Delete Functionality, custom adjustments should be up for reuse
  }

  public function getSingleTicketFeeId(): int
  {
    return Adjustment::query()->where('code', 'SINGLE_TICKET_FEE')->value('id');
  }

  public function getPaidInFullId(): int
  {
    return Adjustment::query()->where('code', 'PAID_IN_FULL')->value('id');
  }

  public function getTaxAdjustmentId(): int
  {
    return Adjustment::query()->where('code', 'TAX')->value('id');
  }

  public function getChooseYourCabinFeeId(): int
  {
    return Adjustment::query()->where('code', 'CHOOSE_YOUR_CABIN')->value('id');
  }

  public function getCarbonOffsetFeeId($code): int
  {
    return Adjustment::query()->where('code', $code)->value('id');
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
