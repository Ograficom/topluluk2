<?php

namespace App\Filament\Pages;

use App\Models\ThemeSetting;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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
            'font_heading_file' => $settings->font_heading_file,
            'font_heading_fallback' => $settings->font_heading_fallback,
            'font_body_file' => $settings->font_body_file,
            'font_body_fallback' => $settings->font_body_fallback,
            'font_button_file' => $settings->font_button_file,
            'font_button_fallback' => $settings->font_button_fallback,
            'font_nav_file' => $settings->font_nav_file,
            'font_nav_fallback' => $settings->font_nav_fallback,
            'font_code_file' => $settings->font_code_file,
            'font_code_fallback' => $settings->font_code_fallback,
            'global_text_scale' => $settings->global_text_scale,
            'custom_css_file' => $settings->custom_css_file,
            'custom_css' => $settings->custom_css,
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
                    TextInput::make('global_text_scale')
                        ->label('Genel yazi/arayuz boyutu (%)')
                        ->numeric()
                        ->minValue(50)
                        ->maxValue(200)
                        ->step(5)
                        ->placeholder('100')
                        ->helperText('Tum sitedeki yazi ve arayuz elemanlarini tek seferde buyutur/kucultur (orn. 110 = %10 daha buyuk). Tek tek her bilesenin font-size kuralini ezmek yerine, dogrudan tarayici olcegini degistirir - bu yuzden gercekten TUM frontend uzerinde etkilidir.'),
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

            Section::make('Yazi Tipleri (Tipografi)')
                ->description('Sitedeki fontlari rol bazinda yonet: her rol icin kendi .woff2/.woff/.ttf/.otf dosyani yukleyebilir veya sadece yedek (fallback) font ismi girebilirsin. Dosya yuklenmezse yedek font kullanilir.')
                ->collapsible()
                ->schema([
                    FileUpload::make('font_heading_file')
                        ->label('Baslik fontu (h1-h6) dosyasi')
                        ->disk('public')
                        ->directory('fonts')
                        ->acceptedFileTypes(['.woff2', '.woff', '.ttf', '.otf'])
                        ->helperText('Basliklarda kullanilacak ozel font dosyasi.'),
                    TextInput::make('font_heading_fallback')
                        ->label('Baslik yedek font adi')
                        ->placeholder('Poppins, Arial, sans-serif'),

                    FileUpload::make('font_body_file')
                        ->label('Govde metni fontu dosyasi')
                        ->disk('public')
                        ->directory('fonts')
                        ->acceptedFileTypes(['.woff2', '.woff', '.ttf', '.otf'])
                        ->helperText('Paragraf, yorum, gonderi metni gibi genel govde yazilari.'),
                    TextInput::make('font_body_fallback')
                        ->label('Govde metni yedek font adi')
                        ->placeholder('Inter, Arial, sans-serif'),

                    FileUpload::make('font_button_file')
                        ->label('Buton fontu dosyasi')
                        ->disk('public')
                        ->directory('fonts')
                        ->acceptedFileTypes(['.woff2', '.woff', '.ttf', '.otf'])
                        ->helperText('Butonlar ve tiklanabilir aksiyon yazilari.'),
                    TextInput::make('font_button_fallback')
                        ->label('Buton yedek font adi')
                        ->placeholder('Inter, Arial, sans-serif'),

                    FileUpload::make('font_nav_file')
                        ->label('Menu / navigasyon fontu dosyasi')
                        ->disk('public')
                        ->directory('fonts')
                        ->acceptedFileTypes(['.woff2', '.woff', '.ttf', '.otf'])
                        ->helperText('Sol menu, sekmeler, kategori listesi gibi navigasyon yazilari.'),
                    TextInput::make('font_nav_fallback')
                        ->label('Menu yedek font adi')
                        ->placeholder('Inter, Arial, sans-serif'),

                    FileUpload::make('font_code_file')
                        ->label('Kod / monospace fontu dosyasi')
                        ->disk('public')
                        ->directory('fonts')
                        ->acceptedFileTypes(['.woff2', '.woff', '.ttf', '.otf'])
                        ->helperText('Kod bloklari ve sabit genislikli yazilar.'),
                    TextInput::make('font_code_fallback')
                        ->label('Kod yedek font adi')
                        ->placeholder('Consolas, Menlo, monospace'),
                ])
                ->columns(2),

            Section::make('Ozel Tema (Kendi CSS Kodun)')
                ->description('Kendi temani/CSS kodunu yukle veya yapistir. Bu, yukaridaki tum renk/font/boyut ayarlarinin da ustune biner - yani istedigin her seyi buradan ezebilirsin. Once dosya, sonra asagidaki kod kutusu uygulanir (kod kutusu her zaman son soz sahibidir).')
                ->collapsible()
                ->schema([
                    FileUpload::make('custom_css_file')
                        ->label('CSS dosyasi yukle (.css)')
                        ->disk('public')
                        ->directory('custom-theme')
                        ->acceptedFileTypes(['.css', 'text/css'])
                        ->helperText('Tam bir stylesheet dosyasi yukleyebilirsin, siteye otomatik uygulanir.'),
                    Textarea::make('custom_css')
                        ->label('Ozel CSS kodu')
                        ->rows(16)
                        ->placeholder(".og-cover {\n  background: #111 !important;\n}")
                        ->helperText('Buraya yazdigin/yapistirdigin CSS, sayfanin en sonunda uygulanir; yukaridaki her ayari (ve yukledigin dosyayi) ezebilir.')
                        ->extraInputAttributes(['style' => 'font-family: ui-monospace, Consolas, Menlo, monospace; font-size: 13px;']),
                ])
                ->columns(1),
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
            'font_heading_file' => null,
            'font_heading_fallback' => null,
            'font_body_file' => null,
            'font_body_fallback' => null,
            'font_button_file' => null,
            'font_button_fallback' => null,
            'font_nav_file' => null,
            'font_nav_fallback' => null,
            'font_code_file' => null,
            'font_code_fallback' => null,
            'global_text_scale' => null,
            'custom_css_file' => null,
            'custom_css' => null,
        ]);

        Cache::forget('theme-appearance-css');

        $this->mount();

        Notification::make()
            ->title('Varsayilan gorunume donuldu')
            ->success()
            ->send();
    }
}
