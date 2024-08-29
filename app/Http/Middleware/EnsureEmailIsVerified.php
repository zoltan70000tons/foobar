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

      return response()->json([
        'message' => __('auth.user_not_found'),
      ], 404);
    }

    if ($user instanceof MustVerifyEmail && !$user->hasVerifiedEmail()) {

      return response()->json([
        'message' => __('auth.verify_email'),
      ], 409);
    }

    return $next($request);
  }
}
