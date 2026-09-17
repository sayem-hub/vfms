<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleMiddleware
{
    /**
     * Handle an incoming request and apply user or session locale.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = ['en', 'bn'];

        // 1. Check session
        if (Session::has('locale') && in_array(Session::get('locale'), $supportedLocales, true)) {
            App::setLocale(Session::get('locale'));
        }
        // 2. Check authenticated user's preferred locale
        elseif ($request->user() && in_array($request->user()->preferred_locale ?? null, $supportedLocales, true)) {
            App::setLocale($request->user()->preferred_locale);
            Session::put('locale', $request->user()->preferred_locale);
        }
        // 3. Fallback to default config
        else {
            App::setLocale(config('app.locale', 'en'));
        }

        return $next($request);
    }
}
