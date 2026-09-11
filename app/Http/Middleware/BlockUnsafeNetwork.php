<?php

namespace App\Http\Middleware;

use App\Services\StrictLoginNetworkGuard;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class BlockUnsafeNetwork
{
    public function __construct(
        private readonly StrictLoginNetworkGuard $networkGuard,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        try {
            $this->networkGuard->assertAllowed($request);
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
