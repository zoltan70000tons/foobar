<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfUnauthenticatedToOAuthLogin
{
    public function handle($request, Closure $next)
    {
        if (!Auth::check()) {
            $query = http_build_query([
                'client_id' => $request->client_id,
                'redirect_uri' => $request->redirect_uri,
              'response_type' => $request->response_type,
                'state' => $request->state,
                'code_challenge' => $request->code_challenge,
                'code_challenge_method' => $request->code_challenge_method,
            ]);

            return redirect("/oauth/login?$query");
        }

        return $next($request);
    }
}