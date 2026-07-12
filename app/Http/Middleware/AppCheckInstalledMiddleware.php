<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AppCheckInstalledMiddleware
{
    /**
     * Handle an incoming request.
     * @param  Request  $request
     * @param  Closure(Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if ($this->alreadyInstalled()) {
            if ($request->routeIs('installer.*')) {
                abort(404);
            }
        }

        if (! $this->alreadyInstalled() && $request->is('/')) {
            return to_route('installer.index');
        }

        return $next($request);
    }

    public function alreadyInstalled()
    {
        return file_exists(storage_path('installed'));
    }
}
