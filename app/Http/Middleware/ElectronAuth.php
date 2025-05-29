<?php
namespace App\Http\Middleware;

use Closure;

class ElectronAuth
{
  public function handle($request, Closure $next)
  {
    // We can add here if environment is local just skip this middleware


    $headerToken = $request->header('X-Device-Token');
    $validToken = env('ADMIN_DEVICE_TOKEN');

    if ($headerToken !== $validToken) {
        abort(403, 'Unauthorized device');
    }

    return $next($request);
  }
}
