<?php

namespace App\Helpers;

class PriceCalculation {
    /**
     * Calculate the total price per passenger.
     *
     * @param float $cabinPrice The base price of the cabin
     * @param int $cabinCapacity The capacity of the cabin
     * @param bool $isPrivateCabin Whether the cabin is a private cabin (true) or shared (false)
     * @param mixed $selectedAdjustments Collection or array of Adjustment models
     * @return array An array with 'total', 'totalPassenger', 'save', and 'extras' keys.
     */
    public static function calculatePricePerPassenger(
        float $cabinPrice,
        int $cabinCapacity,
        bool $isPrivateCabin,
        $selectedAdjustments,
    ): array {
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
        if ($selectedAdjustments->isNotEmpty()) {
            // Get Total Percentage Based Discounts
            $sumOfPercentagesDiscounts = $selectedAdjustments
                ->where('operation', 'PERCENTAGE')
                ->where('type', 'DISCOUNT')
                ->sum('value');

            // Get Total Fixed Discounts
            $sumOfFixedDiscounts = $selectedAdjustments
                ->where('operation', 'FIXED')
                ->where('type', 'DISCOUNT')
                ->sum('value');
        }

        // Cap the percentage discount at 100%
        $validatedPaymentDiscount = max(0, min($sumOfPercentagesDiscounts, 100));
        $percentageDiscount = $validatedPaymentDiscount / 100;

        // Apply percentage discount first, then fixed discount
        $discountedPrice = $roundToTwoDecimals($cabinPrice * (1 - $percentageDiscount) - $sumOfFixedDiscounts);

        /*
    |--------------------------------------------------------------------------
    | Addons
    |--------------------------------------------------------------------------
    | Calculate the total price of all selected addons.
    */
        $sumOfPercentageAddons = 0;
        $sumOfFixedAddons = 0;

        if ($selectedAdjustments->isNotEmpty()) {
            // Get Total Percentage Based Addons
            $sumOfPercentageAddons = $selectedAdjustments
                ->where('operation', 'PERCENTAGE')
                ->where('type', 'ADDON')
                ->sum('value');

            // Get Total Fixed Addons
            $sumOfFixedAddons = $selectedAdjustments->where('operation', 'FIXED')->where('type', 'ADDON')->sum('value');
        }

        // Calculate total addon cost (both fixed and percentage-based)
        $addonsPercentageValue = $roundToTwoDecimals($cabinPrice * ($sumOfPercentageAddons / 100));
        $sumOfAddons = $addonsPercentageValue + $sumOfFixedAddons;

        // Calculate price per passenger
        $totalPassenger = $roundToTwoDecimals($discountedPrice + $sumOfAddons);

        // Determine total based on cabin type
        $total = $totalPassenger * ($isPrivateCabin ? $cabinCapacity : 1);

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
