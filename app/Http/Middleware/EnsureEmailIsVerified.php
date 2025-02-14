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
          'message' => __('auth.user_not_found'),
        ],
        404
      );
    }

    if ($user->hasRole('Customer') && is_null($user->email_verified_at)) {
      // Store verification status in the session
      session(['email_verified' => false]);

      return response()->json([
        'message' => __('auth.email_not_verified'),
        'status' => 'email_not_verified',
        'user' => [
          'name' => $user->detail->first_name ?? null,
          'email_verified_at' => $user->email_verified_at ?? null,
          'membership_type' => $user->membershipTypes->first()->name ?? null,
          'membership_discount' => $user->membershipTypes->first()->discount_value ?? null,
        ],
      ]);
    }

    // Set the session when the email is verified
    session(['email_verified' => true]);

    return $next($request);
  }
}
