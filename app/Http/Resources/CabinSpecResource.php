<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CabinSpecResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'cabin_number' => $this->cabin_number,
            'deck' => $this->deck,
            'total_berths' => $this->total_berths,
            'lower_bed_type_1' => $this->lower_bed_type_1,
            'lower_bed_type_2' => $this->lower_bed_type_2,
            'upper_berths' => $this->upper_berths,
            'accessible' => $this->accessible,
            'connects_with' => $this->connects_with,
            'location' => $this->location,
            'balcony' => $this->balcony,
            'obstructed_view' => $this->obstructed_view,
            'is_shared_cabin_number' => $this->is_shared_cabin_number,
        ];
    }
}
