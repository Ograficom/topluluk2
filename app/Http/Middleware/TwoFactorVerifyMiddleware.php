<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorVerifyMiddleware
{
    protected function user()
    {
        $user = null;
        if (Auth::user()) {
            $user = User::where('id', Auth::user()->id)->first();
        }

        return $user;
    }

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && $this->user()->google2fa_status && ! $request->session()->has('user_2fa') && session('user_2fa') != encrypt($this->user()->id)) {
            return redirect()->route('login.2fa');
        }

        return $next($request);
    }
}
