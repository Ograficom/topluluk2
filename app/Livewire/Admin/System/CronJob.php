<?php

namespace App\Livewire\Admin\System;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Livewire\Component;

class CronJob extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    protected string $url;

    public $cronJobLastExecution;

    public function mount(): void
    {
        $this->url = 'wget -q -O /dev/null '.env('APP_URL').'/cronjob';

        $this->cronJobLastExecution = config('alma.cronjob.last_execution');

        $settings = [
            'key' => config('alma.cronjob.key') ?? '',
        ];

        $this->form->fill($settings);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('Command'))
                    ->description(__('The cron job command must be set to run every minute ( * * * * * )'))
                    ->icon('heroicon-o-command-line')
                    ->schema([
                        TextInput::make('key')
                            ->label(__('Secret key'))
                            ->formatStateUsing(function (string|null $state) {
                                if (! isset($state)) {
                                    return $this->url;
                                }

                                return $this->url.'?key='.$state;
                            })
                            ->readOnly(),
                        ViewField::make('cronJobLastExecution')
                            ->view('filament.forms.components.cronjob_message')
                            ->viewData([
                                'cronJobLastExecution' => $this->cronJobLastExecution,
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    // Generate key
    public function generateKey()
    {
        if (env('DEMO_MODE')) {
            Notification::make()
                ->warning()
                ->title('Opps! You are in demo mode')
                ->seconds(10)
                ->send();

            return;
        }

        Config::write('alma.cronjob.key', Str::random(32));

        Notification::make()
            ->success()
            ->title(__('Cron Job key generated successfully'))
            ->seconds(10)
            ->send();

        $this->js('window.location.reload()');
    }

    // Remove key
    public function removeKey()
    {
        if (env('DEMO_MODE')) {
            Notification::make()
                ->warning()
                ->title('Opps! You are in demo mode')
                ->seconds(10)
                ->send();

            return;
        }

        if (config('alma.cronjob.key') !== '') {
            Config::write('alma.cronjob.key', '');

            Notification::make()
                ->success()
                ->title(__('Cron Job key removed successfully'))
                ->seconds(10)
                ->send();

            $this->js('window.location.reload()');
        }
    }

    public function render()
    {
        return view('livewire.admin.system.cron-job');
    }
}
