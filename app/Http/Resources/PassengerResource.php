<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Traits\SanitizesResponse;

class PassengerResource extends JsonResource
{
  use SanitizesResponse;

  protected static ?array $masking = null;

  public static function passengerMasking(?array $masking)
  {
    self::$masking = $masking;
    return new static(null);
  }

  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    // Base payload (status quo)
    $data = collect(parent::toArray($request))
      ->except(['created_at', 'updated_at'])
      ->toArray();

    // No masking rules, return full payload
    if (!self::$masking) {
      return $this->full($data);
    }

    $fullIds = self::$masking['full_access_ids'] ?? [];
    $limitedFields = self::$masking['limited_fields'] ?? ['id', 'first_name'];

    // Full access passenger
    if (in_array($this->id, $fullIds, true)) {
      return $this->full($data);
    }

    // Limited passenger
    // return collect($data)->only($limitedFields)->toArray();
    return $this->limited($data, $limitedFields);
  }

  /**
   * Build limited passenger payload
   */
  protected function limited(array $data, array $fields): array
  {
    $limited = collect($data)->only($fields)->toArray();

    $sanitizeRules = self::$masking['limited_sanitize'] ?? [];

    if ($sanitizeRules) {
      $limited = $this->sanitize($limited, $sanitizeRules);
    }

    return $limited;
  }

  /**
   * Build full passenger payload with sanitization
   */
  protected function full(array $data): array
  {
    return array_merge($data, [
      'email' => $this->lead_passenger
        ? $this->email
        : $this->sanitize(['email' => $this->email], ['email' => 'email'])['email'],
      'phone' => $this->sanitize(['phone' => $this->phone], ['phone' => 'phone'])['phone'],
      'emergency_c_phone' => $this->sanitize(
        ['emergency_c_phone' => $this->emergency_c_phone],
        ['emergency_c_phone' => 'phone']
      )['emergency_c_phone'],
      'address_first' => $this->sanitize(['address_first' => $this->address_first], ['address_first' => 'address'])[
        'address_first'
      ],
      'address_second' => $this->sanitize(['address_second' => $this->address_second], ['address_second' => 'address'])[
        'address_second'
      ],
      'postal_code' => $this->sanitize(['postal_code' => $this->postal_code], ['postal_code' => 'postal_code'])[
        'postal_code'
      ],
    ]);
  }
}
