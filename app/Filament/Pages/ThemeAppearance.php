<?php

namespace App\Filament\Pages;

use App\Models\ThemeSetting;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
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
            'categories_name_color' => $settings->categories_name_color,
            'categories_stats_color' => $settings->categories_stats_color,
            'categories_description_color' => $settings->categories_description_color,
            'categories_accent_color' => $settings->categories_accent_color,
            'categories_hover_bg_color' => $settings->categories_hover_bg_color,
            'categories_border_color' => $settings->categories_border_color,
            'categories_avatar_size' => $settings->categories_avatar_size,
            'categories_name_font_size' => $settings->categories_name_font_size,
            'categories_stats_font_size' => $settings->categories_stats_font_size,
            'categories_description_font_size' => $settings->categories_description_font_size,
        ]);
    }

    protected function getFormStatePath(): string
    {
        return 'state';
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make('Genel Gorunum')
                ->schema([
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
                ])
                ->columns(2),

            Section::make('Kategoriler Sayfasi Ayarlari')
                ->description('Kategoriler listesindeki kartlarin font boyutu ve renklerini ozellestir.')
                ->collapsible()
                ->schema([
                    TextInput::make('categories_avatar_size')
                        ->label('Avatar boyutu (px)')
                        ->numeric()
                        ->minValue(24)
                        ->maxValue(80)
                        ->placeholder('36'),
                    TextInput::make('categories_name_font_size')
                        ->label('Kategori adi font boyutu (px)')
                        ->numeric()
                        ->step(0.5)
                        ->minValue(10)
                        ->maxValue(28)
                        ->placeholder('14.5'),
                    TextInput::make('categories_stats_font_size')
                        ->label('Istatistik (aboneler/gonderiler) font boyutu (px)')
                        ->numeric()
                        ->step(0.5)
                        ->minValue(9)
                        ->maxValue(20)
                        ->placeholder('12.5'),
                    TextInput::make('categories_description_font_size')
                        ->label('Aciklama font boyutu (px)')
                        ->numeric()
                        ->step(0.5)
                        ->minValue(10)
                        ->maxValue(20)
                        ->placeholder('13'),
                    ColorPicker::make('categories_name_color')
                        ->label('Kategori adi rengi')
                        ->placeholder('#000000'),
                    ColorPicker::make('categories_stats_color')
                        ->label('Istatistik metni rengi')
                        ->placeholder('#71717a'),
                    ColorPicker::make('categories_description_color')
                        ->label('Aciklama metni rengi')
                        ->placeholder('#5f6472'),
                    ColorPicker::make('categories_accent_color')
                        ->label('Vurgu (mavi) rengi')
                        ->helperText('Katilimci sayisi gibi vurgulu alanlarda kullanilir.')
                        ->placeholder('#2563eb'),
                    ColorPicker::make('categories_hover_bg_color')
                        ->label('Kart hover arka plan rengi')
                        ->placeholder('#f9fafb'),
                    ColorPicker::make('categories_border_color')
                        ->label('Ayrac cizgisi rengi')
                        ->placeholder('#e4e4e7'),
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
            'categories_name_color' => null,
            'categories_stats_color' => null,
            'categories_description_color' => null,
            'categories_accent_color' => null,
            'categories_hover_bg_color' => null,
            'categories_border_color' => null,
            'categories_avatar_size' => null,
            'categories_name_font_size' => null,
            'categories_stats_font_size' => null,
            'categories_description_font_size' => null,
        ]);

        Cache::forget('theme-appearance-css');

        $this->mount();

        Notification::make()
            ->title('Varsayilan gorunume donuldu')
            ->success()
            ->send();
    }
}
