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
   * @param  string|null  $currentDateTime
   * @param  int|null  $id
   * @return array
   */
  public function checkMembershipAccess($membership, $currentDateTime = null, $id = null)
  {
    // Set current date and time in EDT timezone
    $currentDateTime = $currentDateTime
      ? Carbon::parse($currentDateTime, 'America/New_York')
      : Carbon::now('America/New_York');

    // Get event by ID
    $event = Event::find($id);
    if (!$event) {
      Log::error("Event not found with ID: $id");
      return ['status' => false];
    }

    // Allow access to booking if event is public
    // No matter if user has membership or not
    if ($event->status === 'PUBLIC') {
      return ['status' => true];
    }

    // Block access to presale events if user has no membership
    // Returns error message to prompt user to sign in to determine membership
    if (!$membership && $event->status === 'PRE-SALE') {
      return [
        'status' => false,
        'message' => 'PRESALE_NO_ACCOUNT',
      ];
    }

    // Query the presale periods table to get the relevant dates
    $presalePeriod = $membership ? PresalePeriod::where('membership_type_id', $membership->id)->first() : null;

    // Interpret start_date and end_date as America/New_York
    $startDateTime = Carbon::createFromFormat(
      'Y-m-d H:i:s',
      $presalePeriod->start_date,
      'America/New_York' // Treat as stored in EDT
    );

    $endDateTime = Carbon::createFromFormat(
      'Y-m-d H:i:s',
      $presalePeriod->end_date,
      'America/New_York' // Treat as stored in EDT
    );

    // Convert to UTC for consistent frontend communication
    $startDateTimeUTC = $startDateTime->copy()->setTimezone('UTC');

    // Check if membership is not allowed to access presale events
    // By comparing the current time with the membership presale start time
    if ($event->status === 'PRE-SALE' && $currentDateTime->lt($startDateTime)) {
      return [
        'status' => false,
        'message' => [
          'code' => 'NO_MEMBERSHIP_ACCESS',
          'membership' => $membership->name,
          'start_time' => $startDateTimeUTC->toIso8601String(), // UTC time for frontend
        ],
      ];
    }

    // Allow access to booking if the current time is within the presale period for the membership
    if ($currentDateTime->gte($startDateTime) && $currentDateTime->lte($endDateTime)) {
      return [
        'status' => true,
        'message' => 'ALLOWED_MEMBERSHIP_ACCESS',
      ];
    }

    // Default: bookings are closed
    return ['status' => false, 'message' => 'CLOSED'];
  }
}
