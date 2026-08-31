<?php

namespace App\Filament\Pages\Auth;

use App\Models\RecaptchaSetting;
use App\Services\LoginSecurityService;
use App\Services\RecaptchaV3Verifier;
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
        app(LoginSecurityService::class)->assertRequestAllowed(request(), $settings);

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

        return parent::authenticate();
    }
}
