<?php
namespace App\Http\Middleware;
use Illuminate\Support\Facades\Auth;

use Closure;

class TeamContext
{
  public function handle($request, Closure $next)
  {
    //$teamId = Auth::user()->currentTeam->id ?? 1;

    // if (Auth::check()) {
    //   // Set it only if not already set
    //   if (!getPermissionsTeamId()) {
    //     setPermissionsTeamId(1);
    //   }
    // }

    // return $next($request);

    setPermissionsTeamId(1);

    return $next($request);
  }
}
