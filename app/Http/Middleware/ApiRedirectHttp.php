<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class ApiRedirectHttp
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    // Allow guests to access the application normally
    if (Auth::check() || $request->expectsJson()) {
      return $next($request);
    }

    // If the request does not expect JSON (likely accessed directly in the browser), redirect to the frontend login page
    $frontendUrl = config('app.frontend_url', env('FRONTEND_URL', 'https://yourfrontend.com'));
    return redirect()->to($frontendUrl . '/en/login');
  }
}
