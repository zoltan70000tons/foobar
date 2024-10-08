<?php

namespace App\Helpers;


use App\Models\SurvivorNumber;

class CustomerHelper {

  /**
   * Generate a unique survivor number
   *
   * @return string
   */
  public static function generateSurvivorNumber(): string
  {
      do {
        // Generate a random 9-digit number
        $survivorNumber = str_pad(mt_rand(1, 999999999), 9, '0', STR_PAD_LEFT);
      } while (SurvivorNumber::where('survivor_number', $survivorNumber)->exists());

      return $survivorNumber;
  }
}