<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\TemporaryReservation;
use Carbon\Carbon;

class ClearExpiredReservation
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    // Check if the session has a 'reserved_cabin_id'
    if ($request->session()->has('reserved_cabin_id')) {
      $reservationId = $request->session()->get('reserved_cabin_id');

      $reservation = TemporaryReservation::find($reservationId);

      // Remove the reserved_cabin_id from the session
      if (!$reservation || Carbon::now()->greaterThan($reservation->expires_at)) {
        $request->session()->forget('reserved_cabin_id');
      }
    }

    return $next($request);
  }
}
