<?php

namespace App\Livewire\Admin\System;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Livewire\Component;

class MaintenanceMode extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        $this->authorize('access_admin_dashboard');

        $settings = [
            'site_maintenance_mode' => settings()->group('general')->get('site_maintenance_mode'),
            'title' => config('alma.maintenance.title'),
            'message' => config('alma.maintenance.message'),
            'secret' => config('alma.maintenance.secret'),
        ];

        $this->form->fill($settings);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('Maintenance Mode'))
                    ->description(__("It's a great way to notify visitors that your site is down but will be back up shortly."))
                    ->icon('heroicon-o-exclamation-triangle')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'md' => 2,
                        ])
                            ->schema([
                                ViewField::make('message')
                                    ->view('filament.forms.components.maintenance_message'),
                                Toggle::make('site_maintenance_mode')
                                    ->label(__('Maintenance Mode'))
                                    ->onIcon('heroicon-m-bolt')
                                    ->offIcon('heroicon-m-bolt-slash')
                                    ->live(onBlur: true),
                            ]),
                        TextInput::make('title')
                            ->label(__('Title'))
                            ->minLength(2)
                            ->maxLength(150)
                            ->visible(fn (Get $get) => $get('site_maintenance_mode') === true),
                        TextInput::make('message')
                            ->label(__('Message'))
                            ->minLength(2)
                            ->maxLength(250)
                            ->visible(fn (Get $get) => $get('site_maintenance_mode') === true),
                        TextInput::make('secret')
                            ->label(__('Secret key'))
                            ->readOnly()
                            ->visible(fn (Get $get) => $get('site_maintenance_mode') === true),
                        ViewField::make('message')
                            ->view('filament.forms.components.maintenance_url')
                            ->visible(fn (Get $get) => $get('site_maintenance_mode') === true),
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

        if ($this->form->getState()['site_maintenance_mode'] === true) {
            settings()->group('general')->set('site_maintenance_mode', $this->form->getState()['site_maintenance_mode']);

            // Set maintenance mode settings
            Config::write('alma.maintenance.title', str_replace(["'", '"'], '', $this->form->getState()['title']));
            Config::write('alma.maintenance.message', str_replace(["'", '"'], '', $this->form->getState()['message']));

            // Put site in maintnenace mode
            Artisan::call('down', ['--secret' => config('alma.maintenance.secret')]);

            // Clear config cache
            Artisan::call('config:clear');
        } else {
            settings()->group('general')->set('site_maintenance_mode', $this->form->getState()['site_maintenance_mode']);

            // Site is up
            Artisan::call('up');

            // Clear cache
            Artisan::call('config:clear');
        }

        Notification::make()
            ->success()
            ->title(__('Settings successfully updated'))
            ->seconds(10)
            ->send();
    }

    public function render()
    {
        return view('livewire.admin.system.maintenance-mode');
    }
}
