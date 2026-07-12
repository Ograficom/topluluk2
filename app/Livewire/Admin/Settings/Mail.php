<?php

namespace App\Livewire\Admin\Settings;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;
use Livewire\Component;

class Mail extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        $settings = [
            'current_mail_driver' => settings()->group('advanced')->get('current_mail_driver'),
            'mail_mailer' => env('MAIL_MAILER'),
            'mail_host' => env('MAIL_HOST'),
            'mail_port' => env('MAIL_PORT'),
            'mail_username' => env('MAIL_USERNAME'),
            'mail_password' => env('MAIL_PASSWORD'),
            'mail_encryption' => env('MAIL_ENCRYPTION'),
            'mail_from_address' => env('MAIL_FROM_ADDRESS'),
            'mail_from_name' => env('MAIL_FROM_NAME'),
        ];

        $this->form->fill($settings);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('Mail Driver'))
                    ->description(__('We need mail configuration for registration, password reset system etc.'))
                    ->icon('heroicon-o-envelope')
                    ->schema([
                        Select::make('current_mail_driver')
                            ->label(__('Mail Driver'))
                            ->options([
                                'log' => 'Log',
                                'smtp' => 'SMTP',
                            ])
                            ->default('log')
                            ->selectablePlaceholder(false)
                            ->native(false)
                            ->live(onBlur: true),
                        TextInput::make('mail_host')
                            ->label(__('Mail Host'))
                            ->placeholder('smtp.domain.com')
                            ->visible(fn (Get $get) => $get('current_mail_driver') === 'smtp'),
                        TextInput::make('mail_port')
                            ->label(__('Mail Port'))
                            ->placeholder('1025')
                            ->visible(fn (Get $get) => $get('current_mail_driver') === 'smtp'),
                        TextInput::make('mail_username')
                            ->label(__('Mail Username'))
                            ->visible(fn (Get $get) => $get('current_mail_driver') === 'smtp'),
                        TextInput::make('mail_password')
                            ->label(__('Mail Password'))
                            ->visible(fn (Get $get) => $get('current_mail_driver') === 'smtp'),
                        TextInput::make('mail_encryption')
                            ->label(__('Mail Encryption'))
                            ->visible(fn (Get $get) => $get('current_mail_driver') === 'smtp'),
                        TextInput::make('mail_from_address')
                            ->label(__('Mail from Address'))
                            ->visible(fn (Get $get) => $get('current_mail_driver') === 'smtp'),
                        TextInput::make('mail_from_name')
                            ->label(__('Mail from Name'))
                            ->visible(fn (Get $get) => $get('current_mail_driver') === 'smtp'),
                    ]),
            ])
            ->statePath('data');
    }

    public function store()
    {
        if (env('DEMO_MODE')) {
            Notification::make()
                ->warning()
                ->title('Opps! You are in demo mode')
                ->seconds(10)
                ->send();

            return;
        }

        if ($this->form->getState()['current_mail_driver'] === 'log') {
            settings()->group('advanced')->set('current_mail_driver', $this->form->getState()['current_mail_driver']);

            Artisan::call('config:clear');

            setEnvironmentValue([
                'mail_mailer' => $this->form->getState()['current_mail_driver'],
            ]);
        }

        if ($this->form->getState()['current_mail_driver'] === 'smtp') {
            settings()->group('advanced')->set('current_mail_driver', $this->form->getState()['current_mail_driver']);

            Artisan::call('config:clear');

            setEnvironmentValue([
                'mail_mailer' => $this->form->getState()['current_mail_driver'],
                'mail_host' => $this->form->getState()['mail_host'],
                'mail_port' => $this->form->getState()['mail_port'],
                'mail_username' => $this->form->getState()['mail_username'],
                'mail_password' => $this->form->getState()['mail_password'],
                'mail_encryption' => $this->form->getState()['mail_encryption'],
                'mail_from_address' => $this->form->getState()['mail_from_address'],
                'mail_from_name' => $this->form->getState()['mail_from_name'],
            ]);
        }

        Notification::make()
            ->success()
            ->title(__('Settings successfully updated'))
            ->seconds(10)
            ->send();
    }

    public function render()
    {
        return view('livewire.admin.settings.mail');
    }
}
