<?php

namespace App\Repositories;

use App\Enums\MemberShip;
use App\Models\Booking;
use App\Models\Passenger;
use App\Models\Adjustment;
use App\Models\SurvivorNumber;
use App\Models\User;
use Illuminate\Support\Collection;

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
      // Fetch all adjustments in one query
      $adjustments = Adjustment::whereIn('id', $adjustmentIds)->get();

      $prepared = $this->prepareAdjustmentsForBooking($adjustments, $booking);

      // Sync the prepared adjustments
      $booking->adjustments()->sync($prepared->pluck('id')->toArray());
      $booking->setRelation('adjustments', $prepared);

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

  /**
   * Prepare adjustment collection with restriction metadata for a specific booking.
   */
  public function prepareAdjustmentsForBooking(Collection $adjustments, Booking $booking, bool $filterForEvent = true): Collection
  {
    $event = $booking->event;
    $context = $this->buildContextFromBooking($booking);

    if ($filterForEvent) {
      $adjustments = $adjustments
        ->filter(function ($adjustment) use ($event) {
          if ($event && $event->status === 'PUBLIC' && str_starts_with($adjustment->code, 'MEMBERSHIP_')) {
            return false;
          }

          return true;
        })
        ->values();
    }

    return $adjustments->map(function ($adjustment) use ($context) {
      $restrictionApplies = method_exists($adjustment, 'shouldApply') ? $adjustment->shouldApply($context) : true;
      $effectiveValue = $this->calculateEffectiveValue($adjustment, $restrictionApplies);

      $adjustment->setAttribute('restriction_applies', $restrictionApplies);
      $adjustment->setAttribute('effective_value', $effectiveValue);
      $adjustment->value = $effectiveValue;

      return $adjustment;
    });
  }

  protected function calculateEffectiveValue(Adjustment $adjustment, bool $restrictionApplies): float
  {
    $value = (float) $adjustment->value;

    if ($adjustment->code === 'CHOOSE_YOUR_CABIN' && $restrictionApplies) {
      return 0.0;
    }

    return $value;
  }

  protected function buildContextFromBooking(Booking $booking): array
  {
    $selectedCabin = $booking->cabin;

    return [
      'cabin' => [
        'category_name' => $selectedCabin?->category?->category_name,
        'code' => $selectedCabin?->category?->category_code,
        'capacity' => $selectedCabin?->category?->capacity,
      ],
    ];
  }
}
