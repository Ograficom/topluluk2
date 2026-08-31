<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Http\Responses\FailedPasswordResetLinkRequestResponse;
use App\Models\RecaptchaSetting;
use App\Models\User;
use App\Services\LoginSecurityService;
use App\Services\RecaptchaV3Verifier;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Contracts\FailedPasswordResetLinkRequestResponse as FailedPasswordResetLinkRequestResponseContract;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            FailedPasswordResetLinkRequestResponseContract::class,
            FailedPasswordResetLinkRequestResponse::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        Fortify::authenticateUsing(function (Request $request) {
            $securitySettings = RecaptchaSetting::currentOrNull();
            $loginSecurity = app(LoginSecurityService::class);

            $loginSecurity->assertRequestAllowed($request, $securitySettings);

            if ($securitySettings && $securitySettings->isEnabledFor('login')) {
                $token = (string) $request->input('recaptcha_token', '');
                if ($token === '') {
                    throw ValidationException::withMessages([
                        Fortify::username() => 'reCAPTCHA doğrulaması gerekli.',
                    ]);
                }

                $result = app(RecaptchaV3Verifier::class)->verify($token, 'login', $request->ip());
                if (! ($result['success'] ?? false)) {
                    throw ValidationException::withMessages([
                        Fortify::username() => 'reCAPTCHA doğrulaması başarısız.',
                    ]);
                }
            }

            $user = User::query()
                ->where(Fortify::username(), (string) $request->input(Fortify::username()))
                ->first();

            if ($user && Hash::check((string) $request->input('password'), (string) $user->password)) {
                $loginSecurity->verifyOrChallenge($user, $request, $securitySettings);

                return $user;
            }

            return null;
        });

        RateLimiter::for('login', function (Request $request) {
            $email = Str::transliterate(Str::lower((string) $request->input(Fortify::username())));
            $ip = (string) $request->ip();

            return [
                Limit::perMinute(5)->by($email.'|'.$ip),
                Limit::perMinute(20)->by('login-ip|'.$ip),
            ];
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
