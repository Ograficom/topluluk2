<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DisableDebugbarOnFrontend
{
    /**
     * Disable Debugbar on public pages so crawlers only see real links.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->bound('debugbar') && !$request->is('admin*')) {
            \Debugbar::disable();
        }

        return $next($request);
    }
}
