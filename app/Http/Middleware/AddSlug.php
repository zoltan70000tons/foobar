<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AddSlug
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
           $slug = auth()->user()->organizations->first()->slug;
           //dd($slug);
           $request->route()->setParameter('slug', $slug);
        }

        return $next($request);
    }
}