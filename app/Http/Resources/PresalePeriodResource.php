<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PresalePeriodResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'membership_type' => $this->membershipType
                ? [
                    'name' => $this->membershipType->name,
                    'stamp_image' => $this->membershipType->stamp_image,
                    'booking_number_requirement' => $this->membershipType->booking_number_requirement,
                    'discount_value' => $this->membershipType->discount_value,
                ]
                : null,
        ];
    }
}
