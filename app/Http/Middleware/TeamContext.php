<?php
namespace App\Http\Middleware;
use Illuminate\Support\Facades\Auth;

use Closure;

class TeamContext
{
  public function handle($request, Closure $next)
  {
    //$teamId = Auth::user()->currentTeam->id ?? 1;

    setPermissionsTeamId(1);

    return $next($request);
  }
}
