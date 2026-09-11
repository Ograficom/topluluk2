<?php

namespace App\Http\Middleware;

use App\Services\ProxyCheckNetworkGuard;
use App\Services\StrictLoginNetworkGuard;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class BlockUnsafeNetwork
{
    public function __construct(
        private readonly StrictLoginNetworkGuard $networkGuard,
        private readonly ProxyCheckNetworkGuard $proxyCheckGuard,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Primary guard: IPQS when configured + official/local Tor/VPN/proxy/
            // datacenter lists with last-known-good cache and fail-closed logic.
            $this->networkGuard->assertAllowed($request);

            // Secondary live opinion catches exits that have not reached the
            // downloaded lists yet. Keep feature tests deterministic; the
            // ProxyCheck service itself has dedicated HTTP-faked tests.
            if (! app()->environment('testing')) {
                $this->proxyCheckGuard->assertAllowed($request);
            }
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())
                ->flatten()
                ->filter(fn ($value) => is_string($value) && trim($value) !== '')
                ->first();

            abort(
                Response::HTTP_FORBIDDEN,
                is_string($message) && $message !== ''
                    ? $message
                    : 'Bu ağ üzerinden erişime izin verilmiyor.',
            );
        }

        return $next($request);
    }
}
