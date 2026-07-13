<?php

namespace App\Http\Middleware;

use App\Support\InstallState;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInstalled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!InstallState::isInstalled()) {
            config([
                'session.driver' => 'file',
                'cache.default' => 'file',
            ]);

            if (!$request->is('install*')) {
                return redirect()->route('install.requirements');
            }
        }

        return $next($request);
    }
}
