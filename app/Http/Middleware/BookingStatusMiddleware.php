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
    // Allow API authentication routes and CSRF cookie requests
    if ($request->is('sanctum/csrf-cookie') || $request->is('api/*')) {
      return $next($request);
    }

    // if booking status is differnet than "ON-HOLD" do not allow to update booking
    $bookingCode = $request->route('bookingCode');
    $booking = Booking::where('booking_code', $bookingCode)->first();

    if (!$booking) {
      return response()->json(['message' => 'Booking not found.'], 403);
    }

    // Allow viewing if status is ON-HOLD or COMPLETED
    if (!in_array($booking->status, ['ON HOLD', 'COMPLETED'])) {
      return response()->json(['message' => 'Access denied.'], 403);
    }

    return $next($request);
  }
}
