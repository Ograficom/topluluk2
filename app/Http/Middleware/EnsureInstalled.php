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

            // Crawlers and deployment checks must always be able to read robots.txt,
            // even while the application is still waiting for its first installation.
            if (!$request->is('install*') && !$request->is('robots.txt')) {
                return redirect()->route('install.requirements');
            }
        }

        return $next($request);
    }
}
