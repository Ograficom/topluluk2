<?php

namespace App\Filament\Pages\Auth;

use App\Models\RecaptchaSetting;
use App\Services\LoginSecurityService;
use App\Services\RecaptchaV3Verifier;
use App\Services\StrictLoginNetworkGuard;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                TextInput::make('device_verification_code')
                    ->label('Yeni cihaz doğrulama kodu')
                    ->helperText('Bu cihaz daha önce doğrulanmadıysa e-postana gönderilen 6 haneli kodu gir.')
                    ->autocomplete('one-time-code')
                    ->inputMode('numeric')
                    ->minLength(6)
                    ->maxLength(6)
                    ->required(fn (): bool => app(LoginSecurityService::class)->hasPendingDeviceChallenge(request()))
                    ->visible(fn (): bool => app(LoginSecurityService::class)->hasPendingDeviceChallenge(request())),
                $this->getRememberFormComponent(),
                TextInput::make('website')
                    ->label('Website')
                    ->autocomplete('off')
                    ->extraFieldWrapperAttributes([
                        'style' => 'position:fixed;left:-10000px;top:-10000px;width:1px;height:1px;overflow:hidden;opacity:0;pointer-events:none;',
                        'aria-hidden' => 'true',
                    ])
                    ->extraInputAttributes([
                        'tabindex' => '-1',
                        'autocomplete' => 'off',
                    ]),
                Hidden::make('recaptcha_token'),
                View::make('filament.auth.admin-recaptcha'),
            ]);
    }

    public function authenticate(): ?LoginResponse
    {
        $settings = RecaptchaSetting::currentOrNull();
        $loginSecurity = app(LoginSecurityService::class);

        try {
            app(StrictLoginNetworkGuard::class)->assertAllowed(request(), $settings);
            $loginSecurity->assertRequestAllowed(request(), $settings);
        } catch (ValidationException $exception) {
            $this->throwAsFilamentValidation($exception);
        }

        $data = $this->form->getState();

        if (($settings?->bot_honeypot_enabled ?? true) && trim((string) ($data['website'] ?? '')) !== '') {
            throw ValidationException::withMessages([
                'data.email' => 'Giriş doğrulanamadı.',
            ]);
        }

        if ($settings?->isEnabledFor('admin')) {
            $token = trim((string) ($data['recaptcha_token'] ?? ''));

            if ($token === '') {
                $this->dispatch('admin-recaptcha-refresh');

                throw ValidationException::withMessages([
                    'data.email' => 'Robot doğrulaması gerekli. Lütfen tekrar dene.',
                ]);
            }

            $result = app(RecaptchaV3Verifier::class)->verify($token, 'admin_login', request()->ip());

            $this->data['recaptcha_token'] = null;
            $this->dispatch('admin-recaptcha-refresh');

            if (! ($result['success'] ?? false)) {
                throw ValidationException::withMessages([
                    'data.email' => 'Robot doğrulaması başarısız. Lütfen tekrar dene.',
                ]);
            }
        }

        $response = parent::authenticate();

        if ($response === null) {
            return null;
        }

        $user = filament()->auth()->user();
        if (! $user) {
            return $response;
        }

        request()->merge([
            'device_verification_code' => (string) ($data['device_verification_code'] ?? ''),
        ]);

        try {
            $loginSecurity->verifyOrChallenge($user, request(), $settings);
        } catch (ValidationException $exception) {
            // Filament has already authenticated the credentials at this point.
            // Keep the device challenge session, but remove the authenticated user
            // until the verification code is accepted on the next submit.
            filament()->auth()->logout();

            $this->throwAsFilamentValidation($exception);
        }

        return $response;
    }

    private function throwAsFilamentValidation(ValidationException $exception): never
    {
        $errors = $exception->errors();
        $message = $errors['email'][0]
            ?? collect($errors)->flatten()->first()
            ?? 'Giriş doğrulanamadı.';

        throw ValidationException::withMessages([
            'data.email' => (string) $message,
        ]);
    }
}
