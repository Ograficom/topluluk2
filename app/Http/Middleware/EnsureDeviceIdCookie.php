<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureDeviceIdCookie
{
    public const COOKIE_NAME = 'ogra_device_id';

    public function handle(Request $request, Closure $next): Response
    {
        $deviceId = $request->cookie(self::COOKIE_NAME) ?? (string) Str::uuid();
        $request->attributes->set('device_id', $deviceId);

        $response = $next($request);

        if (!$request->cookies->has(self::COOKIE_NAME)) {
            $response->headers->setCookie(
                cookie(
                    name: self::COOKIE_NAME,
                    value: $deviceId,
                    minutes: 60 * 24 * 365 * 5,
                    path: '/',
                    domain: config('session.domain'),
                    secure: config('session.secure', false),
                    httpOnly: true,
                    sameSite: config('session.same_site', 'lax'),
                )
            );
        }

        return $response;
    }
}
