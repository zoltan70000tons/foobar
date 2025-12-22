<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingInvitationResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'id' => $this->id,
      'token' => $this->token,
      'email' => $this->email,
      'invited_by' => $this->booking->passengers->where('lead_passenger', true)->first()->full_name,
      'booking' => new BookingResource($this->whenLoaded('booking')),
    ];
  }
}
