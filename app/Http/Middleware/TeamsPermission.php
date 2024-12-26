<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\Auth;

class TeamsPermission
{
  public function handle($request, \Closure $next)
  {
    $user = Auth::user();

    if ($user) {
      // session value set on login
      setPermissionsTeamId(session('team_id'));
    }

    return $next($request);
  }
}
