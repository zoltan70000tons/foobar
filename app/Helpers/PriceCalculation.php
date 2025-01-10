<?php

namespace App\Helpers;

class PriceCalculation
{
  /**
   * Calculate the total price per passenger.
   *
   * @param array $params An associative array containing all necessary parameters.
   * @return array An array with 'total', 'totalPassenger', 'save' and extras keys.
   */
  public static function calculatePricePerPassenger(array $params): array
  {
    // Extract parameters from the input array
    $cabinPrice = (float) $params['cabinPrice'];
    $cabinCapacity = (int) $params['cabinCapacity'];
    $cabinType = (bool) $params['cabinType'];
    $selectedAdjustments = $params['selectedAdjustments'];
    $adjustments = $params['adjustments'];

    \Log::info('collected params', $params);

    // Helper function to round to two decimal places
    $roundToTwoDecimals = fn($value) => round($value * 100) / 100;

    /*
    |--------------------------------------------------------------------------
    | Discounts
    |--------------------------------------------------------------------------
    |
    | Calculate the total discount based on the selected addons.
    |
    */
    $sumOfPercentagesDiscounts = 0;
    $sumOfFixedDiscounts = 0;

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

      foreach ($selectedAdjustments as $addon) {
        // Find the matching adjustment
        $adjustment = $adjustments
          ->where('code', $addon['code'])
          ->where('operation', 'FIXED')
          ->where('type', 'DISCOUNT')
          ->first();

        // Add the value only if the adjustment exists
        if ($adjustment && isset($adjustment->value)) {
          $sumOfFixedDiscounts += $adjustment->value;
        }
      }
    }

    $validatedPaymentDiscount = max(0, min($sumOfPercentagesDiscounts, 100));

    // $userDiscountPercentage = $validatedUserDiscount / 100;
    $addonsDiscountPercentage = $validatedPaymentDiscount / 100;
    $sumOfDiscounts = $addonsDiscountPercentage + $sumOfFixedDiscounts;

    // Ensure the total discount doesn't exceed 100%
    $totalDiscountPercentage = min($sumOfDiscounts, 1);

    // Calculate base price after discount
    $discountedPrice = $roundToTwoDecimals($cabinPrice - $cabinPrice * $totalDiscountPercentage);

    \Log::info('discounted price', [
      'validatedPaymentDiscount' => $validatedPaymentDiscount,
      'sumOfPercentagesDiscounts' => $sumOfPercentagesDiscounts,
      'totalDiscountPercentage' => $totalDiscountPercentage,
      'discountedPrice' => $discountedPrice,
    ]);

    /*
    |--------------------------------------------------------------------------
    | Addons
    |--------------------------------------------------------------------------
    |
    | Calculate the total price of all selected addons.
    |
    */
    $sumOfPercentageAddons = 0;
    $sumOfFixedAddons = 0;

    if (is_array($selectedAdjustments) && !empty($selectedAdjustments)) {
      foreach ($selectedAdjustments as $addon) {
        // Find the matching adjustment
        $adjustment = $adjustments
          ->where('code', $addon['code'])
          ->where('operation', 'PERCENTAGE')
          ->where('type', 'ADDON')
          ->first();

        // Add the value only if the adjustment exists
        if ($adjustment && isset($adjustment->value)) {
          $sumOfPercentageAddons += $adjustment->value;
        }
      }

      foreach ($selectedAdjustments as $addon) {
        // Find the matching adjustment
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

    // Add single ticket fee (only for one passenger)
    $totalPassenger = $roundToTwoDecimals($discountedPrice + $sumOfFixedAddons);

    $total = $totalPassenger * ($cabinType ? $cabinCapacity : 1);

    \Log::info('total price', [
      'total' => $total,
      'totalPassenger' => $totalPassenger,
      'sumOfFixedAddons' => $sumOfFixedAddons,
    ]);

    // Calculate total savings
    $saveCalc = $cabinPrice - $discountedPrice;
    $save = number_format($saveCalc, 2, '.', '');

    \Log::info('total savings', [
      'save' => $save,
    ]);

    return [
      'total' => $total,
      'totalPassenger' => $totalPassenger,
      'save' => $save,
      'extras' => $sumOfFixedAddons,
    ];
  }
}
