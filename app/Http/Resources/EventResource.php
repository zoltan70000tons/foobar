<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'image' => $this->image,
            'address' => $this->address,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status,
            'booked_stamp' => $this->booked_stamp,
            'url' => $this->url,
            'adjustments' => AdjustmentsResource::collection($this->whenLoaded('adjustments')),
            'presale_periods' => PresalePeriodResource::collection($this->whenLoaded('presalePeriods')),
        ];
    }
}
