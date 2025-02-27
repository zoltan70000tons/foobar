<?php

namespace App\Helpers;

class PriceCalculation
{
  /**
   * Calculate the total price per passenger.
   *
   * @param array $params An associative array containing all necessary parameters.
   * @return array An array with 'total', 'totalPassenger', 'save', and 'extras' keys.
   */
  public static function calculatePricePerPassenger(array $params): array
  {
    // Extract parameters from the input array
    $cabinPrice = (float) $params['cabinPrice'];
    $cabinCapacity = (int) $params['cabinCapacity'];
    $cabinType = (bool) $params['cabinType'];
    $selectedAdjustments = $params['selectedAdjustments'];
    $adjustments = $params['adjustments'];
    $eventStatus = $params['eventStatus'];

    // Helper function to round to two decimal places
    $roundToTwoDecimals = fn($value) => round($value * 100) / 100;

    /*
    |--------------------------------------------------------------------------
    | Discounts
    |--------------------------------------------------------------------------
    | Calculate the total discount based on the qualifying discounts.
    */
    $sumOfPercentagesDiscounts = 0;
    $sumOfFixedDiscounts = 0;

    // Ensure $selectedAdjustments is an array and not empty
    if (is_array($selectedAdjustments) && !empty($selectedAdjustments)) {
      foreach ($selectedAdjustments as $addon) {
        // Find percentage-based discounts
        $adjustment = $adjustments
          ->where('code', $addon['code'])
          ->where('operation', 'PERCENTAGE')
          ->where('type', 'DISCOUNT')
          ->first();

        // Add the value only if the adjustment exists
        if ($adjustment && isset($adjustment->value)) {
          // Apply MEMBERSHIP discount only if event status is PRE-SALE
          if (strpos($adjustment->code, 'MEMBERSHIP') !== false && $eventStatus !== 'PRE-SALE') {
            continue;
          }
          $sumOfPercentagesDiscounts += $adjustment->value;
        }
      }

      foreach ($selectedAdjustments as $addon) {
        // Find fixed discounts
        $adjustment = $adjustments
          ->where('code', $addon['code'])
          ->where('operation', 'FIXED')
          ->where('type', 'DISCOUNT')
          ->first();

        // Add the value only if the adjustment exists
        if ($adjustment && isset($adjustment->value)) {
          if (strpos($adjustment->code, 'MEMBERSHIP') !== false && $eventStatus !== 'PRE-SALE') {
            continue;
          }
          $sumOfFixedDiscounts += $adjustment->value;
        }
      }
    }

    // Cap the percentage discount at 100%
    $validatedPaymentDiscount = max(0, min($sumOfPercentagesDiscounts, 100));
    $percentageDiscount = $validatedPaymentDiscount / 100;

    // Apply percentage discount first, then fixed discount
    $discountedPrice = $roundToTwoDecimals(($cabinPrice * (1 - $percentageDiscount)) - $sumOfFixedDiscounts);

    /*
    |--------------------------------------------------------------------------
    | Addons
    |--------------------------------------------------------------------------
    | Calculate the total price of all selected addons.
    */
    $sumOfPercentageAddons = 0;
    $sumOfFixedAddons = 0;

    if (is_array($selectedAdjustments) && !empty($selectedAdjustments)) {
      foreach ($selectedAdjustments as $addon) {
        // Find percentage-based addons
        $adjustment = $adjustments
          ->where('code', $addon['code'])
          ->where('operation', 'PERCENTAGE')
          ->where('type', 'ADDON')
          ->first();

        if ($adjustment && isset($adjustment->value)) {
          $sumOfPercentageAddons += $adjustment->value;
        }
      }

      foreach ($selectedAdjustments as $addon) {
        // Find fixed addons
        $adjustment = $adjustments
          ->where('code', $addon['code'])
          ->where('operation', 'FIXED')
          ->where('type', 'ADDON')
          ->first();

        // Add the value only if the adjustment exists
        if ($adjustment && isset($adjustment->value)) {
          $sumOfFixedAddons += $adjustment->value;
        }
      }
    }

    // Calculate total addon cost (both fixed and percentage-based)
    $addonsPercentageValue = $roundToTwoDecimals($cabinPrice * ($sumOfPercentageAddons / 100));
    $sumOfAddons = $addonsPercentageValue + $sumOfFixedAddons;

    // Calculate price per passenger
    $totalPassenger = $roundToTwoDecimals($discountedPrice + $sumOfAddons);

    // Determine total based on cabin type
    $total = $totalPassenger * ($cabinType ? $cabinCapacity : 1);

    // Calculate total savings
    $saveCalc = $cabinPrice - $discountedPrice;
    $save = number_format($saveCalc, 2, '.', '');

    return [
      'total' => $total,
      'totalPassenger' => $totalPassenger,
      'save' => $save,
      'extras' => $sumOfAddons, // Ensure all addons are included
    ];
  }
}
