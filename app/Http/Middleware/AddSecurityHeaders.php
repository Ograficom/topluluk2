<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddSecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \Symfony\Component\HttpFoundation\Response $response */
        $response = $next($request);

        if (!$response instanceof Response) {
            return $response;
        }

        if (!$response->headers->has('Content-Security-Policy')) {
            $response->headers->set('Content-Security-Policy', implode('; ', [
                "default-src 'self' https: data: blob:",
                "base-uri 'self'",
                "object-src 'none'",
                "frame-ancestors 'self'",
                "form-action 'self' https://accounts.google.com",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' https:",
                "style-src 'self' 'unsafe-inline' https:",
                "img-src 'self' data: blob: https:",
                "font-src 'self' data: https:",
                "connect-src 'self' https: wss: ws: blob:",
                "frame-src 'self' https:",
                "media-src 'self' data: blob: https:",
            ]));
        }

        if (!$response->headers->has('Cross-Origin-Opener-Policy')) {
            $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin-allow-popups');
        }

        if (!$response->headers->has('X-Content-Type-Options')) {
            $response->headers->set('X-Content-Type-Options', 'nosniff');
        }

        if (!$response->headers->has('Referrer-Policy')) {
            $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        }

        if (!$response->headers->has('X-Frame-Options')) {
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        }

        if (!$response->headers->has('Permissions-Policy')) {
            $response->headers->set('Permissions-Policy', 'camera=(), geolocation=(), microphone=(), payment=()');
        }

        if (($request->isSecure() || $request->header('X-Forwarded-Proto') === 'https') && !$response->headers->has('Strict-Transport-Security')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Eski PWA service worker'i kaydi olan tarayicilarda sayfa bazen bayat
        // (eski) cache'ten servis edilip yorum/etiket gibi widget'lar bos
        // gorunebiliyordu. Bu header, sayfanin kendi temizlik script'i hic
        // calisamasa bile tarayiciyi service worker'i silmeye zorluyor
        // (genel HTTP cache'e dokunmuyor, performansi etkilemiyor).
        if (!$response->headers->has('Clear-Site-Data')) {
            $response->headers->set('Clear-Site-Data', '"service-workers"');
        }

        return $response;
    }
}
