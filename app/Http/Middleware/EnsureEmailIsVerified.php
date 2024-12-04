<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    $user = $request->user();

    if (!$user) {
      return response()->json(
        [
          "message" => __("auth.user_not_found"),
        ],
        404
      );
    }

    if ($user->hasRole("Customer") && is_null($user->email_verified_at)) {
      return response()
        ->json([
          "message" => __("auth.email_not_verified"),
          "status" => "email_not_verified",
          "user" => $user, // Include user object
        ])
        ->cookie(
          "email_verified", // Cookie name
          "false", // Cookie value
          60 // Expiration in minutes
        );
    }

    if ($user->hasRole("Customer") && !is_null($user->email_verified_at)) {
      return response()->json($user)->cookie(
        "email_verified", // Cookie name
        "true", // Cookie value
        60 // Expiration in minutes
      );
    }

    // Proceed with the request and include the user in the response
    return $next($request);
  }
}
