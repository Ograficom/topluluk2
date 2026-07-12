<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialController extends Controller
{
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->stateless()->redirect();
    }

    public function handleProviderCallback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();

            if ($socialUser->getEmail() > '') {
                $existingUser = User::where('email', '=', $socialUser->getEmail())->first();
                if ($existingUser) {
                    Auth::login($existingUser);

                    return to_route('feed.home');
                }
            } else {
                return to_route('login');
            }

            $userName = ($socialUser->getNickName() != null)
                ? $socialUser->getNickName()
                : $socialUser->getName();

            // Remove special chars
            $userName = remove_special_chars($userName);

            // Convert username to lowercase and del spaces
            $userName = str_replace(' ', '', Str::lower($userName));

            // Check unique username
            $i = 0;
            while (User::where('username', '=', $userName)->exists()) {
                $i++;
                $userName = $userName.$i;
            }

            $user = User::updateOrCreate(
                [
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                ],
                [
                    'name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'avatar' => $socialUser->getAvatar(),
                    'email_verified_at' => now(),
                ]
            );

            $user->username = $userName;
            $user->remember_token = md5($socialUser->getEmail());
            $user->profile()->create();
            $user->save();

            $user->assignRole('author');

            Auth::login($user);
        } catch (Exception $e) {
        }

        return to_route('feed.home');
    }
}
