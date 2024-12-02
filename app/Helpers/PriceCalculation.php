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
    $cabinPrice = (float) $params["cabinPrice"];
    $capacity = (int) $params["capacity"];
    $userDiscount = (float) $params["userDiscount"];
    $cabinType = (bool) $params["cabinType"];
    $paymentDiscount = (float) $params["paymentDiscount"];
    $addons = $params["addons"];
    $isSelection = (bool) $params["isSelection"];
    $singleTicketFeeAddon = (float) $params["singleTicketFeeAddon"];
    $taxAddon = (float) $params["taxAddon"];
    $chooseYourCabinAddon = (float) $params["chooseYourCabinAddon"];

    // Helper function to round to two decimal places
    $roundToTwoDecimals = function ($value) {
      return round($value * 100) / 100;
    };

    // Validate and clamp discounts between 0% and 100%
    $validatedUserDiscount = max(0, min($userDiscount, 100));
    $validatedPaymentDiscount = max(0, min($paymentDiscount, 100));

    $userDiscountPercentage = $validatedUserDiscount / 100;
    $paymentDiscountPercentage = $validatedPaymentDiscount / 100;

    $sumOfDiscounts = $userDiscountPercentage + $paymentDiscountPercentage;

    // Ensure the total discount doesn't exceed 100%
    $totalDiscountPercentage = min($sumOfDiscounts, 1);

    // Calculate base price after discount
    $discountedPrice = $roundToTwoDecimals($cabinPrice - $cabinPrice * $totalDiscountPercentage);

    // Calculate addons price
    $addonsPrice = 0;
    if (is_array($addons) && !empty($addons)) {
      foreach ($addons as $addon) {
        $addonsPrice = $roundToTwoDecimals($addonsPrice + $addon["value"]);
      }
    }

    // Add single ticket fee (only for one passenger)
    $totalAfterSingle = $roundToTwoDecimals($discountedPrice + ($cabinType ? $singleTicketFeeAddon : 0));

    // Calculate total for all passengers
    $totalPassenger = $roundToTwoDecimals($totalAfterSingle + $taxAddon + $addonsPrice);
    $total = $roundToTwoDecimals(
      $totalPassenger * ($cabinType ? $capacity : 1) + ($cabinType === false ? $singleTicketFeeAddon : 0)
    );

    // Add optional selection price (once)
    if ($isSelection) {
      $total += $chooseYourCabinAddon;
    }

    // Calculate total savings
    $saveCalc = $cabinPrice - $discountedPrice;
    $save = number_format($saveCalc, 2, ".", "");

    // Log for debugging
    // \Log::info("Price calculation", [
    //   "cabinPrice" => $cabinPrice,
    //   "capacity" => $capacity,
    //   "userDiscount" => $userDiscount,
    //   "cabinType" => $cabinType,
    //   "paymentDiscount" => $paymentDiscount,
    //   "addons" => $addons,
    //   "isSelection" => $isSelection,
    //   "singleTicketFeeAddon" => $singleTicketFeeAddon,
    //   "taxAddon" => $taxAddon,
    //   "chooseYourCabinAddon" => $chooseYourCabinAddon,
    //   "total" => $total,
    //   "totalPassenger" => $totalPassenger,
    //   "save" => $save,
    // ]);

    return [
      "total" => $total,
      "totalPassenger" => $totalPassenger,
      "save" => $save,
    ];
  }
}
