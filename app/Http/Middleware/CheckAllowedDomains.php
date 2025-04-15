<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAllowedDomains
{
  public function handle(Request $request, Closure $next): Response
  {
    // 1. Check for internal secret (e.g. from Lambda webhook)
    $internalToken = $request->header('X-Webhook-Token');
    $expectedToken = env('LAMBDA_WEBHOOK_TOKEN');

    if ($internalToken && $internalToken === $expectedToken) {
      return $next($request); // trusted internal call
    }

    // 2. Fallback to domain whitelist for browser/client requests
    $allowedDomains = explode(',', env('NOTIFICATION_ALLOWED_DOMAINS', 'localhost'));
    $originHeader = $request->headers->get('origin');
    $origin = parse_url($originHeader ?? '', PHP_URL_HOST) ?: $request->getHost();

    if (!in_array('*', $allowedDomains) && !in_array($origin, $allowedDomains)) {
      return response()->json(['error' => 'Unauthorized domain.'], 403);
    }

    return $next($request);
  }
}
