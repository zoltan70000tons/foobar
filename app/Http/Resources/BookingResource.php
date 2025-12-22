<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Enums\EventStatus;
use App\Http\Resources\EventResource;
use App\Http\Resources\AdjustmentsResource;

class BookingResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    $masking = $request->attributes->get('passenger_masking');

    $isNew = in_array($this->status, ['NEW']);
    $eventClosed = $this->event?->status === EventStatus::CLOSED->value;

    PassengerResource::passengerMasking($masking);

    return [
      'id' => $this->id,
      'status' => $this->status,
      'is_single_occupancy' => $this->is_single_occupancy,
      'bed_config' => $this->bed_config,
      'booking_request_id' => $this->booking_request_id,
      'booking_code' => $isNew ? null : $this->booking_code,
      'payment_plan' => $this->payment_plan,
      'event_id' => $this->event_id,
      'event' => new EventResource($this->whenLoaded('event')),

      'cabin' => $this->when(
        !$eventClosed,
        fn() => new CabinResource($this->whenLoaded('cabin'), $isNew, ($cabinDetails = true))
      ),

      'passengers' => PassengerResource::collection($this->whenLoaded('passengers')),

      'adjustments' => AdjustmentsResource::collection($this->whenLoaded('adjustments')),

      $this->mergeWhen(!$eventClosed, [
        'booking_code' => $this->booking_code,
        'payment_plan' => $this->payment_plan,
      ]),
    ];
  }
}
