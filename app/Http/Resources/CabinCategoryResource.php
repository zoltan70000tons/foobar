<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CabinCategoryResource extends JsonResource {
    public bool $cabinDetails;

    public function __construct($resource, bool $cabinDetails = false) {
        parent::__construct($resource);
        $this->cabinDetails = $cabinDetails;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'price' => $this->price,
            'event_id' => $this->event_id,
            'category_type' => $this->category_type,
            'category_code' => $this->category_code,
            'category_name' => $this->category_name,
            'capacity' => $this->capacity,
            'description' => $this->description,
            'decks' => $this->decks,
            'images' => $this->images,
            'iframe' => $this->iframe,
            'title' => $this->title,
            'high_roller' => $this->high_roller,
            'capacity_description' => $this->capacity_description,
            'cabin_type' => $this->cabinDetails ? new CabinTypeResource($this->whenLoaded('cabinType')) : null,
        ];
    }
}
