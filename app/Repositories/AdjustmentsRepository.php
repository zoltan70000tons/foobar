<?php

namespace App\Repositories;

use App\Enums\MemberShip;
use App\Models\Booking;
use App\Models\Passenger;
use App\Models\Adjustment;
use App\Models\SurvivorNumber;
use App\Models\User;
use Illuminate\Support\Collection;

class AdjustmentsRepository {
    protected Booking $booking;
    protected Passenger $passenger;

    public function __construct(Passenger $passenger, Booking $booking) {
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
    public function attachAdjustments(array $adjustmentIds, Booking $booking): bool {
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

    /**
     * Get Membership Adjustment based on survivor number.
     *
     * @param string|null $survivorNumber The survivor number to look up.
     * @param int $eventId The event ID to filter adjustments.
     * @return Adjustment|null
     */
    public function getAdjustmentsBySurvivorNumber($survivorNumber, int $eventId): Adjustment|null {
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
                $result = Adjustment::where('code', '=', $membership->name)
                    ->where('event_id', $eventId)
                    ->first();
                break;
            }
        }

        return $result;
    }

    /**
     * List all adjustments for a specific event.
     *
     * @param int $eventId The event ID to filter adjustments
     * @param bool $systemOnly Whether to include only system adjustments
     * @return Collection Collection of adjustments
     */
    public function listAdjustments(int $eventId, bool $systemOnly = false): Collection {
        return Adjustment::query()
            ->where('event_id', $eventId)
            ->when($systemOnly, function ($query) {
                $query->where('is_system', true);
            })
            ->get();
    }

    /**
     * Get multiple adjustments by their codes in a single query, filtered by event.
     *
     * @param array $codes Array of adjustment codes to retrieve
     * @param int $eventId The event ID to filter by
     * @return Collection Collection of adjustments keyed by code
     */
    public function getAdjustmentsByCodes(array $codes, int $eventId): Collection {
        return Adjustment::query()->where('event_id', $eventId)->whereIn('code', $codes)->get();
    }

    /**
     * Prepare adjustment collection with restriction metadata for a specific booking.
     */
    public function prepareAdjustmentsForBooking(
        Collection $adjustments,
        Booking $booking,
        bool $filterForEvent = true,
    ): Collection {
        $context = $this->buildContextFromBooking($booking);

        return $adjustments->filter(function ($adjustment) use ($context) {
            $restrictionApplies = method_exists($adjustment, 'shouldApply') ? $adjustment->shouldApply($context) : true;
            return $restrictionApplies;
        });
    }

    protected function buildContextFromBooking(Booking $booking): array {
        $selectedCabin = $booking->cabin;
        $event = $booking->event;

        return [
            'cabin' => [
                'category_name' => $selectedCabin?->category?->category_name,
                'code' => $selectedCabin?->category?->category_code,
                'capacity' => $selectedCabin?->category?->capacity,
            ],
            'event' => [
                'status' => $event->status,
                'start_date' => $event->start_date,
                'end_date' => $event->end_date,
            ],
        ];
    }
}
