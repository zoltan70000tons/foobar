<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CheckBookingSession
{
  public function handle(Request $request, Closure $next)
  {
    if (Session::has('booking_token_expires') && now()->greaterThan(Session::get('booking_token_expires'))) {
      Session::forget(['booking_token', 'booking_token_expires']);
    }

    return $next($request);
  }
}
