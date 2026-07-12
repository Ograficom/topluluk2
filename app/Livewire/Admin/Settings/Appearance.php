<?php

namespace App\Livewire\Admin\Settings;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Livewire\Component;

class Appearance extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        $settings = [
            'default_font' => config('alma.appearance.default_font'),
            'header_color' => config('alma.appearance.header_color'),
            'color_theme' => config('alma.appearance.theme'),
            'radius' => config('alma.appearance.radius'),
        ];

        $this->form->fill($settings);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('Appearance'))
                    ->description(__('Make you own style'))
                    ->icon('heroicon-o-swatch')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'md' => 2,
                        ])
                            ->schema([
                                TextInput::make('default_font')
                                    ->label(__('Default font'))
                                    ->placeholder(__('e.g: Montserrat')),
                                ColorPicker::make('header_color')
                                    ->label(__('Header color (Available only for light theme)')),
                                Select::make('color_theme')
                                    ->label(__('Default color theme'))
                                    ->options([
                                        'amber' => __('Amber'),
                                        'blue' => __('Blue'),
                                        'cyan' => __('Cyan'),
                                        'emerald' => __('Emerald'),
                                        'fuchsia' => __('Fuchsia'),
                                        'green' => __('Green'),
                                        'indigo' => __('Indigo'),
                                        'lime' => __('Lime'),
                                        'orange' => __('Orange'),
                                        'pink' => __('Pink'),
                                        'purple' => __('Purple'),
                                        'red' => __('Red'),
                                        'rose' => __('Rose'),
                                        'sky' => __('Sky'),
                                        'teal' => __('Teal'),
                                        'violet' => __('Violet'),
                                        'yellow' => __('Yellow'),
                                    ])
                                    ->searchable()
                                    ->selectablePlaceholder(false)
                                    ->default('blue'),
                                Select::make('radius')
                                    ->label(__('Default radius'))
                                    ->options([
                                        '0' => __('0'),
                                        '0.25' => __('0.25'),
                                        '0.5' => __('0.5'),
                                        '0.75' => __('0.75'),
                                        '1' => __('1'),
                                    ])
                                    ->searchable()
                                    ->selectablePlaceholder(false)
                                    ->default('0.5'),
                            ]),

                    ]),

            ])
            ->statePath('data');
    }

    public function store()
    {
        if ($this->form->getState()['default_font']) {
            Config::write('alma.appearance.default_font', $this->form->getState()['default_font']);
        }

        if ($this->form->getState()['header_color']) {
            Config::write('alma.appearance.header_color', $this->form->getState()['header_color']);
        }

        if ($this->form->getState()['radius']) {
            Config::write('alma.appearance.radius', $this->form->getState()['radius']);
        }

        if ($this->form->getState()['color_theme']) {
            Config::write('alma.appearance.theme', $this->form->getState()['color_theme']);
        }

        // Clear all cache
        Artisan::call('optimize:clear');

        Notification::make()
            ->success()
            ->title(__('Settings successfully updated'))
            ->seconds(10)
            ->send();
    }

    public function render()
    {
        return view('livewire.admin.settings.appearance');
    }
}
