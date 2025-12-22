<?php

namespace App\Traits;

use App\Helpers\DataMasker;

trait SanitizesResponse
{
  protected function sanitize(array $data, array $maskConfig = []): array
  {
    foreach ($maskConfig as $field => $strategy) {
      if (!isset($data[$field]) || !$data[$field]) {
        continue;
      }

      $data[$field] = match ($strategy) {
        'address' => DataMasker::maskAddress($data[$field]),
        'phone' => DataMasker::maskPhone($data[$field]),
        'email' => DataMasker::maskEmail($data[$field]),
        'dob' => DataMasker::maskDob($data[$field]),
        'full' => DataMasker::maskMiddle($data[$field]),
        'postal_code' => DataMasker::maskPostalCode($data[$field]),
        default => $data[$field],
      };
    }

    return $data;
  }
}
