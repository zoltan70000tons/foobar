<?php

namespace App\Traits;

use Carbon\Carbon;
use App\Models\PresalePeriod;
use App\Models\Event;
use Illuminate\Support\Facades\Log;

trait MembershipAccess
{

  /**
   * Check if the user has access to sales based on their membership type
   * 
   * @param  \App\Models\MembershipType|null  $membership
   * @param  string|null  $currentDate
   * @param  int|null  $id
   * @return array
   */
  public function checkMembershipAccess($membership, $currentDate = null, $id = null)
  {
    $currentDate = $currentDate ?: Carbon::now()->format('Y-m-d');

    // Get event by id
    $event = Event::find($id);

    // Query the presale periods table to get the relevant dates
    $presalePeriod = PresalePeriod::where('membership_type_id', $membership->id ?? null)->first();

    // If no membership, check status of event
    if (!$membership && $event->status !== 'public') {
      return [
        'status' => false,
        'message' => __('auth.no_access_to_sales_everyone')
      ];
    }

    // If no membership, but event status is public
    if (!$membership && $event->status === 'public') {
      return ['status' => true];
    }

    // If membership, check if the current date is after the membership access date
    if (
      $presalePeriod &&
      ($event->status === 'pre-sale' || $event->status === 'public') &&
      $currentDate <= $presalePeriod->start_date
    ) {
      return [
        'status' => false,
        'message' => __('auth.no_access_to_sales_not_allowed_type', ['name' => $membership->name])
      ];
    }

    return ['status' => true];
  }
}
