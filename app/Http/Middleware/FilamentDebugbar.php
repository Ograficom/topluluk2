<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class FilamentDebugbar
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (app()->bound('debugbar')) {
            if (session('debugbar_disabled', false)) {
                \Debugbar::disable();
            } else {
                \Debugbar::enable();
            }
        }

        return $response;
    }
}
