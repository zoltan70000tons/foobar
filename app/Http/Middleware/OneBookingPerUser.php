<?php
namespace App\Http\Middleware;
use Illuminate\Support\Facades\Auth;

use Closure;

class OneBookingPerUser
{
  public function handle($request, Closure $next)
  {
    // Check the user has already booked for this event
    if (
      Auth::check() &&
      Auth::user()
        ->bookings()
        ->where("event_id", $request->event_id)
        ->count() > 0
    ) {
      return response()->json(
        [
          "message" => "You already have a booking for this event",
          "code" => "BOOKING_LIMIT_EXCEEDED",
        ],
        403
      );
    }

    return $next($request);
  }
}
