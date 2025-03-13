<?php
namespace App\Http\Middleware;
use Illuminate\Support\Facades\Auth;
use App\Models\Booking;

use Closure;

class OneBookingPerUser
{
  public function handle($request, Closure $next)
  {
    if (Auth::check()) {
      $user = Auth::user();

      $bookingExists = Booking::where('event_id', $request->event_id)
        ->where('status', '!=', 'CANCELLED')
        ->where(function ($query) use ($user) {
          $query->where('customer_id', $user->id)->orWhereHas('passengers', function ($q) use ($user) {
            $q->where('survivor_number', $user->survivorNumber->survivor_number);
          });
        })
        ->exists();

      if ($bookingExists) {
        return response()->json(
          [
            'message' => 'You already have a booking for this event',
            'code' => 'BOOKING_LIMIT_EXCEEDED',
          ],
          403
        );
      }
    }

    return $next($request);
  }
}
