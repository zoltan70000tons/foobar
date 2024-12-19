<?php

namespace App\Helpers;

class PriceCalculation
{
  /**
   * Calculate the total price per passenger.
   *
   * @param array $params An associative array containing all necessary parameters.
   * @return array An array with 'total', 'totalPassenger', and 'save' keys.
   */
  public static function calculatePricePerPassenger(array $params): array
  {
    // Extract parameters from the input array
    $cabinPrice = (float) $params['cabinPrice'];
    $cabinCapacity = (int) $params['cabinCapacity'];
    $userDiscount = (float) $params['userDiscount'];
    $cabinType = (bool) $params['cabinType'];
    $selectedAdjustments = $params['selectedAdjustments'];
    $adjustments = $params['adjustments'];

    // Helper function to round to two decimal places
    $roundToTwoDecimals = function ($value) {
      return round($value * 100) / 100;
    };

    // Validate and clamp discounts between 0% and 100%
    $validatedUserDiscount = max(0, min($userDiscount, 100));

    $sumOfPercentagesDiscounts = 0;
    // Ensure $selectedAdjustments is an array and not empty
    if (is_array($selectedAdjustments) && !empty($selectedAdjustments)) {
      foreach ($selectedAdjustments as $addon) {
        // Find the matching adjustment
        $adjustment = $adjustments
          ->where('code', $addon['code'])
          ->where('operation', 'PERCENTAGE')
          ->where('type', 'DISCOUNT')
          ->first();

        // Add the value only if the adjustment exists
        if ($adjustment && isset($adjustment->value)) {
          $sumOfPercentagesDiscounts += $adjustment->value;
        }
      }
    }

    $validatedPaymentDiscount = max(0, min($sumOfPercentagesDiscounts, 100));

    $userDiscountPercentage = $validatedUserDiscount / 100;
    $addonsDiscountPercentage = $validatedPaymentDiscount / 100;

    $sumOfDiscounts = $userDiscountPercentage + $addonsDiscountPercentage;

    // Ensure the total discount doesn't exceed 100%
    $totalDiscountPercentage = min($sumOfDiscounts, 1);

    // Calculate base price after discount
    $discountedPrice = $roundToTwoDecimals($cabinPrice - $cabinPrice * $totalDiscountPercentage);

    // addons
    $sumOfAddons = 0;
    if (is_array($selectedAdjustments) && !empty($selectedAdjustments)) {
      foreach ($selectedAdjustments as $addon) {
        // Find the matching adjustment
        $adjustment = $adjustments
          ->where('code', $addon['code'])
          ->where('operation', 'FIXED')
          ->where('type', 'ADDON')
          ->first();

        // Add the value only if the adjustment exists
        if ($adjustment && isset($adjustment->value)) {
          $sumOfAddons += $adjustment->value;
        }
      }
    }

    // Add single ticket fee (only for one passenger)
    $totalPassenger = $roundToTwoDecimals($discountedPrice + $sumOfAddons);

    $total = $totalPassenger * ($cabinType ? $cabinCapacity : 1);

    // Calculate total savings
    $saveCalc = $cabinPrice - $discountedPrice;
    $save = number_format($saveCalc, 2, '.', '');

    return [
      'total' => $total,
      'totalPassenger' => $totalPassenger,
      'save' => $save,
      'extras' => $sumOfAddons,
    ];
  }
}
