<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\Recaptcha;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'regex:/^[0-9A-Za-z\-_]+$/u', 'min:3', 'max:36', 'unique:users'],
            'email' => ['required', 'string', 'lowercase', 'email', 'indisposable', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'recaptcha' => $request->recaptcha == null ? ['nullable', 'sometimes'] : ['required', new Recaptcha($request->recaptcha)],
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if (env('MAIL_MAILER') === 'log') {
            $user->update([
                'email_verified_at' => now(),
            ]);
        }

        $user->profile()->create();

        $user->assignRole('author');

        $user->update([
            'preference_settings' => [
                'show_nsfw' => true,
                'blur_nsfw' => true,
                'open_posts_new_tab' => false,
            ],
            'notify_settings' => [
                'new_comments' => true,
                'replies_comments' => true,
                'liked' => true,
                'new_follower' => true,
                'mentions' => true,
            ],
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('feed.home', absolute: false));
    }
}
