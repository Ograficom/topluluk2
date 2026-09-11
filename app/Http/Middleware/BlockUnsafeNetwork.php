<?php

namespace App\Http\Middleware;

use App\Models\RecaptchaSetting;
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
            // Global web protection must not depend on the old login-only toggles.
            // Build an in-memory policy from the dedicated site-wide config.
            $globalPolicy = new RecaptchaSetting([
                'block_vpn_logins' => (bool) config('login-security.global_block_vpn', true),
                'block_tor_logins' => (bool) config('login-security.global_block_tor', true),
            ]);

            // Primary guard: IPQS when configured + official/local Tor/VPN/proxy/
            // datacenter lists with last-known-good cache and fail-closed logic.
            $this->networkGuard->assertAllowed($request, $globalPolicy);

            // Secondary live opinion. This catches VPN/proxy exits that are not yet
            // present in the static lists. Works keyless with a small daily budget.
            $this->proxyCheckGuard->assertAllowed($request);
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
