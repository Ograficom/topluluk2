<?php

namespace App\Http\Middleware;

use App\Support\InstallState;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfInstalled
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs('install.finished')) {
            return $next($request);
        }

        if (InstallState::isInstalled()) {
            return redirect('/');
        }

        return $next($request);
    }
}
