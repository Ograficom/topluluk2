<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RegistrationDisableMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $registration = config('alma.registration');

        if (! $registration && $request->route()->getName() == 'register') {
            toast_warning(__('Registration is disabled. For questions about you can write from Contact form!'));

            return redirect()->route('feed.home');
        }

        return $next($request);
    }
}
