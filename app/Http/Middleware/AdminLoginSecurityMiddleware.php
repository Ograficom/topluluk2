<?php

namespace App\Http\Middleware;

use App\Models\RecaptchaSetting;
use App\Services\LoginSecurityService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminLoginSecurityMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            app(LoginSecurityService::class)->assertRequestAllowed(
                $request,
                RecaptchaSetting::currentOrNull(),
            );
        }

        /** @var Response $response */
        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');

        return $response;
    }
}
