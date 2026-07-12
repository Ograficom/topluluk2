<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfUserSuspended
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (isAppInstalled()) {
            if (Auth::check() && Auth::user()->isSuspended()) {
                Auth::logout();
                toast_warning(__('This account is suspended. For questions about unlocking you can write from Contact form!'));

                // TODO::// ADD Modal for this
                return redirect()->route('feed.home');
            }
        }

        return $next($request);
    }
}
