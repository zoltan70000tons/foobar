<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use App\Traits\MembershipAccess;

class MembershipSales
{

  use MembershipAccess;


  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function handle(Request $request, Closure $next): Response
  {

    // Check if the current date is after the "Everyone" access date
    if ($this->checkMembershipAccess(null)['status'] === true) {
      return $next($request);
    }

    // Get the authenticated customer by guard
    if (!Auth::guard('customer')->check()) {
      return response()->json([
        'message' => __('auth.unauthenticated_access_type', ['guard' => 'customer']),
        'status' => 'no_public_access',
      ], 401);
    }

    $customer = Auth::guard('customer')->user();
    $membership = $customer->membershipTypes->first();

    $access = $this->checkMembershipAccess($membership);

    if ($access['status'] === false) {
      return response()->json([
        'message' => $access['message'],
        'status' => 'no_type_access',
      ], 403);
    }

    return $next($request);
  }
}
