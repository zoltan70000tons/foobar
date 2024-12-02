<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;
use App\Traits\MembershipAccess;
use App\Models\Event;

class MembershipSales
{
  use MembershipAccess;

  public function handle(Request $request, Closure $next): Response
  {
    $language = $request->query("language", "en");
    $id = $request->route("id") ?? null;

    // log cookies sended
    // Log::info("--- Incoming Request ---");
    // Log::info("Request URL: " . $request->fullUrl());
    // Log::info("Request Method: " . $request->method());
    // Log::info("Request IP Address: " . $request->ip());
    // Log::info("Request Headers: " . json_encode($request->headers->all()));
    // Log::info("Request Cookies: " . json_encode($request->cookies->all()));
    // Log::info("Request Query Parameters: " . json_encode($request->query()));
    // Log::info("Request Payload: " . json_encode($request->all()));

    App::setLocale($language);

    // check if the Auth
    $user = Auth::check() ? Auth::user() : null;
    $customer = $user && $user->hasRole("Customer") ? $user : null;

    //Log::info("Customer: " . json_encode($customer));

    $membership = null;

    if ($customer) {
      $membership = $customer->membershipTypes->first() ?? null;
    }

    $access = $this->checkMembershipAccess($membership, null, $id);

    // Attach access information to the request
    $request->merge([
      "purchase_access" => $access["status"],
      "access_message" => $access["message"] ?? null,
    ]);

    return $next($request);
  }
}
