<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsNotCustomer {
    /**
     * Make sure the customer have not access to the admin panel.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response {
        // Allow API authentication routes and CSRF cookie requests
        if ($request->is('sanctum/csrf-cookie') || $request->is('api/*')) {
            return $next($request);
        }

        $user = Auth::user();

        if ($user) {
            setPermissionsTeamId(1);

            if ($user->hasRole('Customer')) {
                return response()->json(
                    [
                        'message' => __('You do not have access to this resource.'),
                    ],
                    403,
                );
            }
        }

        // Allow non-customer users to proceed
        return $next($request);
    }
}
