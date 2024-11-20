<?php
namespace App\Http\Middleware;
use Illuminate\Support\Facades\Auth;

use Closure;

class OneBookingPerUser
{
  public function handle($request, Closure $next)
  {
    // Middleware to check if user has already booked

    return $next($request);
  }
}
