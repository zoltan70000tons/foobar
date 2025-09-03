<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfUnauthenticatedToOAuthLogin
{
    public function handle($request, Closure $next)
    {
        // If the user is not authenticated, redirect to the OAuth login page
        if (!Auth::check()) {
            $frontURL = config('app.frontend_url');
            return redirect()->to($frontURL);
        }

        return $next($request);
    }
}