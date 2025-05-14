<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class EnsureEmailIsVerified
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    if (!Auth::check()) {
      Cookie::queue(Cookie::forget('email_verified'));
    } elseif (Auth::user()->hasRole('Customer') && is_null(Auth::user()->email_verified_at)) {
      Cookie::queue(cookie('email_verified', 'false', 60));
    } else {
      Cookie::queue(cookie('email_verified', 'true', 60));
    }

    return $next($request);
  }
}
