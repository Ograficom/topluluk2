<?php

namespace App\Filament\Pages;

use App\Models\ThemeSetting;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;

class ThemeAppearance extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.theme-appearance';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-swatch';
    protected static string | \UnitEnum | null $navigationGroup = 'Site';
    protected static ?string $navigationLabel = 'Gorunum Ayarlari';

    public array $state = [];

    public function mount(): void
    {
        $settings = ThemeSetting::current();

        $this->form->fill([
            'brand_background_color' => $settings->brand_background_color,
            'brand_surface_color' => $settings->brand_surface_color,
            'brand_button_color' => $settings->brand_button_color,
            'brand_button_hover_color' => $settings->brand_button_hover_color,
            'brand_button_text_color' => $settings->brand_button_text_color,
            'brand_text_color' => $settings->brand_text_color,
            'brand_font_family' => $settings->brand_font_family,
        ]);
    }

    protected function getFormStatePath(): string
    {
        return 'state';
    }

    protected function getFormSchema(): array
    {
        return [
            ColorPicker::make('brand_background_color')
                ->label('Arka plan rengi')
                ->helperText('Sayfanin genel arka plan rengi. Bos birakilirsa varsayilan renk kullanilir.'),
            ColorPicker::make('brand_surface_color')
                ->label('Kart / yuzey rengi')
                ->helperText('Header, kartlar ve panellerin arka plan rengi.'),
            ColorPicker::make('brand_button_color')
                ->label('Buton / vurgu rengi')
                ->helperText('Ana butonlar, linkler ve vurgu rengi.'),
            ColorPicker::make('brand_button_hover_color')
                ->label('Buton hover rengi')
                ->helperText('Butonun uzerine gelindiginde kullanilacak renk.'),
            ColorPicker::make('brand_button_text_color')
                ->label('Buton yazi rengi'),
            ColorPicker::make('brand_text_color')
                ->label('Genel yazi rengi'),
            Select::make('brand_font_family')
                ->label('Site fontu')
                ->options([
                    'Roboto' => 'Roboto',
                    'Inter' => 'Inter',
                    'Poppins' => 'Poppins',
                    'Nunito' => 'Nunito',
                    'Open Sans' => 'Open Sans',
                    'Montserrat' => 'Montserrat',
                    'system-ui' => 'Sistem fontu',
                ])
                ->native(false)
                ->placeholder('Varsayilan (Roboto)'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        ThemeSetting::current()->update($data);

        Cache::forget('theme-appearance-css');

        Notification::make()
            ->title('Gorunum ayarlari guncellendi')
            ->success()
            ->send();
    }

    public function resetToDefaults(): void
    {
        ThemeSetting::current()->update([
            'brand_background_color' => null,
            'brand_surface_color' => null,
            'brand_button_color' => null,
            'brand_button_hover_color' => null,
            'brand_button_text_color' => null,
            'brand_text_color' => null,
            'brand_font_family' => null,
        ]);

        Cache::forget('theme-appearance-css');

        $this->mount();

        Notification::make()
            ->title('Varsayilan gorunume donuldu')
            ->success()
            ->send();
    }
}
