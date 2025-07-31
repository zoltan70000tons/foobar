<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\App;
use Closure;

class ElectronAuth
{
  public function handle($request, Closure $next)
  {
    // if environmt is local, skip the middleware
    if (App::environment('local') || App::environment('development')) {
      return $next($request);
    }


    $headerToken = $request->header('X-Device-Token');
    $validToken = env('ADMIN_DEVICE_TOKEN');

    if ($headerToken !== $validToken) {
      abort(403, 'Unauthorized device');
    }

    return $next($request);
  }
}
