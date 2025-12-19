<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Traits\SanitizesResponse;

class CustomerResource extends JsonResource
{
  use SanitizesResponse;

  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'name' => $this->detail?->first_name,
      'membership_type' => $this->membershipTypes->first()?->name,
      'membership_discount' => $this->membershipTypes->first()?->discount_value,
      'email' => $this->email,
      'email_verified_at' => $this->email_verified_at,
      'survivor_number' => $this->survivorNumber?->survivor_number,

      'details' => $this->detail
        ? $this->sanitize($this->detail->makeHidden(['id', 'user_id', 'created_at', 'updated_at'])->toArray(), [])
        : null,

      'address' => $this->customerAddress
        ? $this->sanitize(
          $this->customerAddress->makeHidden(['id', 'user_id', 'created_at', 'updated_at'])->toArray(),
          []
        )
        : null,
    ];
  }
}
