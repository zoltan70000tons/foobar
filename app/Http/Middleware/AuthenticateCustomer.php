<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;


class AuthenticateCustomer
{
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function handle(Request $request, Closure $next): Response
  {

    if (Auth::check() && Auth::user()->hasRole('Customer')) {
      return $next($request);
    }

    return response()->json([
        'message' => __('auth.unauthenticated'),
    ], 401);

    // if (Auth::guard('customer')->check()) {
    //   $request->session()->put('type_of_guard', 'customer');
    //   return $next($request);
    // }

    // // Return a JSON response with a clear error message and status code
    // return response()->json([
    //   'message' => __('auth.unauthenticated', ['guard' => 'customer']),
    // ], 401);
  }
}
