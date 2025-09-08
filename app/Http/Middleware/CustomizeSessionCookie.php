<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CustomizeSessionCookie
{
    /**
     * Adjust the session cookie name (and optionally scope) per area.
     */
    public function handle(Request $request, Closure $next)
    {
        // Default cookie name from config
        $defaultCookie = config('session.cookie');

        // OAuth pages under /oauth use a separate cookie
        if ($request->is('oauth') || $request->is('oauth/*')) {
            $cookieName = env('SESSION_COOKIE_OAUTH', $defaultCookie.'_oauth');
            config(['session.cookie' => $cookieName]);

            // Optionally scope to path and/or domain if provided
            if ($path = env('SESSION_PATH_OAUTH')) {
                config(['session.path' => $path]);
            }
            if ($domain = env('SESSION_DOMAIN_OAUTH')) {
                config(['session.domain' => $domain]);
            }
        }

        return $next($request);
    }
}

