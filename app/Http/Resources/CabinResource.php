<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CabinResource extends JsonResource
{
  protected bool $isNewOrCancelled;
  protected bool $cabinDetails;

  public function __construct($resource, bool $isNewOrCancelled = true, bool $cabinDetails = false)
  {
    parent::__construct($resource);
    $this->isNewOrCancelled = $isNewOrCancelled;
    $this->cabinDetails = $cabinDetails;
  }

  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'id' => $this->id,
      'cabin_type_id' => $this->cabin_type_id,
      'notes' => $this->notes,
      'status' => $this->status,
      'cabin_number' => $this->isNewOrCancelled ? null : $this->cabin_number,
      'deck' => $this->deck,
      'balcony' => $this->balcony,
      'obstructed_view' => $this->obstructed_view,
      'location' => $this->location,
      'accessible' => $this->accessible,

      'category' => new CabinCategoryResource($this->whenLoaded('category'), $this->cabinDetails),
      'cabin_type' => $this->cabinDetails ? new CabinTypeResource($this->whenLoaded('cabinType')) : null,
      'cabin_spec' =>
        $this->isNewOrCancelled === false && $this->cabinDetails
          ? new CabinSpecResource($this->whenLoaded('cabinSpec'))
          : null,
    ];

    // return parent::toArray($request);
  }
}
