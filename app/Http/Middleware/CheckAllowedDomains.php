<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAllowedDomains
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowedDomains = explode(',', env('NOTIFICATION_ALLOWED_DOMAINS', 'localhost'));
        $origin = parse_url($request->headers->get('origin') ?? '', PHP_URL_HOST) ?: $request->getHost();
        if (!in_array('*', $allowedDomains) && !in_array($origin, $allowedDomains)) {
            return response()->json(['error' => 'Unauthorized domain.'], 403);
        }
        return $next($request);
    }
}
