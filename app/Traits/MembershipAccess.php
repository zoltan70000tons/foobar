<?php

namespace App\Traits;

use Carbon\Carbon;
use App\Models\PresalePeriod;
use Illuminate\Support\Facades\Log;

trait MembershipAccess
{

  /**
   * Check if the user has access to sales based on their membership type
   * 
   * @param  \App\Models\MembershipType|null  $membership
   * @param  string|null  $currentDate
   * @return array
   */
  public function checkMembershipAccess($membership, $currentDate = null)
  {
    $currentDate = $currentDate ?: Carbon::now()->format('Y-m-d');
    $public_sales = '2024-12-01';

    // Query the presale periods table to get the relevant dates
    $presalePeriod = PresalePeriod::where('membership_type_id', $membership->id ?? null)->first();

    // If no membership, check if the current date is after the "Everyone" access date
    if (!$membership && $currentDate < $public_sales) {
      return [
        'status' => false,
        'message' => __('auth.no_access_to_sales_everyone')
      ];
    }

    // If no membership, but the current date is after the "Everyone" access date
    if (!$membership && $currentDate >= $public_sales) {
      return ['status' => true];
    }

    // If membership, check if the current date is after the membership access date
    if ($presalePeriod) {
      return [
        'status' => false,
        'message' => __('auth.no_access_to_sales_not_allowed_type', ['name' => $membership->name])
      ];
    }

    return ['status' => true];
  }
}
