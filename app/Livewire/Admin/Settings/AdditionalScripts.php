<?php

namespace App\Livewire\Admin\Settings;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Livewire\Component;

class AdditionalScripts extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        $settings = settings()->group('advanced')->all(false);
        $settings = [
            'google_analytics_code' => $settings['google_analytics_code'] ?? '',
            'custom_head_code' => $settings['custom_head_code'] ?? '',
            'custom_footer_code' => $settings['custom_footer_code'] ?? '',
        ];
        $this->form->fill($settings);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('Google Analytics'))
                    ->description(__('Google Analytics allows you to track visitors to your site and generates reports that will help you with your marketing.'))
                    ->schema([
                        Textarea::make('google_analytics_code')
                            ->autosize()
                            ->rows(5),
                    ]),
                Section::make(__('Custom code'))
                    ->description(__('You may want to add some html/css/js code. For example custom js or other meta tags etc.'))
                    ->icon('heroicon-o-code-bracket')
                    ->schema([
                        Textarea::make('custom_head_code')
                            ->autosize()
                            ->rows(6),
                        Textarea::make('custom_footer_code')
                            ->autosize()
                            ->rows(6),
                    ]),
            ])
            ->statePath('data');
    }

    public function store(): void
    {
        if (env('DEMO_MODE')) {
            Notification::make()
                ->warning()
                ->title('Opps! You are in demo mode')
                ->seconds(10)
                ->send();

            return;
        }
        isset($this->form->getState()['google_analytics_code'])
            ? settings()->group('advanced')->set('google_analytics_code', $this->form->getState()['google_analytics_code'])
            : settings()->group('advanced')->set('google_analytics_code', '');

        isset($this->form->getState()['custom_head_code'])
            ? settings()->group('advanced')->set('custom_head_code', $this->form->getState()['custom_head_code'])
            : settings()->group('advanced')->set('custom_head_code', '');

        isset($this->form->getState()['custom_footer_code'])
            ? settings()->group('advanced')->set('custom_footer_code', $this->form->getState()['custom_footer_code'])
            : settings()->group('advanced')->set('custom_footer_code', '');

        Notification::make()
            ->success()
            ->title(__('Settings successfully updated'))
            ->seconds(10)
            ->send();
    }

    public function render()
    {
        return view('livewire.admin.settings.additional-scripts');
    }
}
