<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateOrganization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $routeParameters = $request->route()->parameters();
        if (array_key_exists('slug', $routeParameters)){
            $slug = $routeParameters['slug'];
            $org = Organization::where('slug', $slug)->first();
            if ($org) {
                if ($user->organizations->contains($org->id)) {
                    return $next($request);
                } else {
                    abort(404,'Page not found.');
                }
            } else {
                abort(404,'Page not found.');
            }
        }
        return $next($request);
    }
}
