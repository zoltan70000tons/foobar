<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsNotWeb
{
  /**
   * Make sure the customer have not access to the admin panel.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    // if (Auth::guard('web')->check()) {
    //   // return 401 response if the user is a customer
    //   return response()->json([
    //     'message' => __('auth.unauthenticated' . ' web', ['guard' => 'web']),
    //   ], 401);
    // }

    // Proceed with the request if it's not a customer
    return $next($request);
  }
}
