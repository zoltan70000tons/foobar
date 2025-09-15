<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocaleFromRequest
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $supported = ['en', 'es', 'de'];

        $locale = $request->query('language')
            ?? $request->route('language')
            ?? $request->session()->get('locale')
            ?? config('app.locale');

        if (! in_array($locale, $supported, true)) {
            $locale = config('app.fallback_locale', 'en');
        }

        app()->setLocale($locale);

        if ($request->has('language')) {
            $request->session()->put('locale', $locale);
        }

        return $next($request);
    }
}

