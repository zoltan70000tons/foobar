<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Booking;
use Carbon\Carbon;

class BookingStatusMiddleware
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    // if booking status is differnet than "ON-HOLD" do not allow to update booking

    $bookingCode = $request->route('bookingCode');

    $booking = Booking::where('booking_code', $bookingCode)->first();

    if ($booking && $booking->status !== 'ON HOLD') {
      return response()->json(['message' => 'Booking status is not "ON HOLD".'], 403);
    }

    return $next($request);
  }
}
