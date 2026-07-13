<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $availableLocales = array_keys(config('app.available_locales', []));
        $defaultLocale = config('app.locale', 'tr');

        $locale = $request->query('lang');
        if (is_string($locale) && in_array($locale, $availableLocales, true)) {
            $request->session()->put('locale', $locale);
        }

        $activeLocale = $request->session()->get('locale', $defaultLocale);
        if (!in_array($activeLocale, $availableLocales, true)) {
            $activeLocale = $defaultLocale;
        }

        App::setLocale($activeLocale);

        return $next($request);
    }
}
