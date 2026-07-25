<?php

namespace App\Filament\Pages;

use App\Models\ThemeSetting;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;

class SidebarWidgets extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.sidebar-widgets';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-view-columns';
    protected static string | \UnitEnum | null $navigationGroup = 'Site';
    protected static ?string $navigationLabel = 'Sag Sutun Widget Ayarlari';

    public array $state = [];

    public function mount(): void
    {
        $settings = ThemeSetting::current();

        $this->form->fill([
            'widget_comments_enabled' => $settings->widget_comments_enabled,
            'widget_comments_count' => $settings->widget_comments_count,
            'widget_tags_enabled' => $settings->widget_tags_enabled,
            'widget_tags_count' => $settings->widget_tags_count,
            'widget_trending_enabled' => $settings->widget_trending_enabled,
            'widget_trending_count' => $settings->widget_trending_count,
        ]);
    }

    protected function getFormStatePath(): string
    {
        return 'state';
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make('Son yorumlar')
                ->schema([
                    Toggle::make('widget_comments_enabled')
                        ->label('Widget acik olsun')
                        ->helperText('Kapatilirsa "Son yorumlar" kutusu sag sutunda gorunmez.'),
                    TextInput::make('widget_comments_count')
                        ->label('Gosterilecek yorum sayisi')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(30)
                        ->required(),
                ])
                ->columns(2),

            Section::make('Populer etiketler')
                ->schema([
                    Toggle::make('widget_tags_enabled')
                        ->label('Widget acik olsun')
                        ->helperText('Kapatilirsa "Populer etiketler" kutusu sag sutunda gorunmez.'),
                    TextInput::make('widget_tags_count')
                        ->label('Gosterilecek etiket sayisi')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(30)
                        ->required(),
                ])
                ->columns(2),

            Section::make('Populer gonderiler (en cok goruntulenen / en cok tepki alan)')
                ->schema([
                    Toggle::make('widget_trending_enabled')
                        ->label('Widget acik olsun')
                        ->helperText('Kapatilirsa "Son yorumlar" altindaki populer gonderiler kutusu gorunmez.'),
                    TextInput::make('widget_trending_count')
                        ->label('Her listede gosterilecek gonderi sayisi')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(20)
                        ->required(),
                ])
                ->columns(2),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        ThemeSetting::current()->update($data);

        Cache::forget('theme-appearance-css');

        Notification::make()
            ->title('Widget ayarlari guncellendi')
            ->success()
            ->send();
    }
}
