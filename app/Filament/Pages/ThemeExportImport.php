<?php

namespace App\Filament\Pages;

use App\Models\ThemeSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ThemeExportImport extends Page
{
    protected string $view = 'filament.pages.theme-export-import';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-arrow-down-tray';
    protected static string | \UnitEnum | null $navigationGroup = 'Ayarlar';
    protected static ?string $navigationLabel = 'Tema Indir / Yukle';
    protected static ?int $navigationSort = 25;

    /** ThemeSetting fields that store a path on the "public" disk rather than a plain value. */
    private const FILE_FIELDS = [
        'header_logo_image',
        'font_heading_file',
        'font_body_file',
        'font_button_file',
        'font_nav_file',
        'font_code_file',
        'custom_css_file',
    ];

    /**
     * Groups every ThemeSetting field under the site area it actually affects, so the
     * downloaded package reads as a page-by-page/area-by-area breakdown instead of one
     * flat list. A couple of groups are marked "(su an pasif)" - those columns exist on
     * the model but nothing in the current codebase renders them (verified by tracing
     * every read site); they're still exported for completeness/forward-compatibility,
     * just labelled honestly instead of implying they currently do something.
     */
    private const FIELD_SECTIONS = [
        'brand_background_color' => 'Genel Gorunum',
        'brand_surface_color' => 'Genel Gorunum',
        'brand_button_color' => 'Genel Gorunum',
        'brand_button_hover_color' => 'Genel Gorunum',
        'brand_button_text_color' => 'Genel Gorunum',
        'brand_text_color' => 'Genel Gorunum',
        'brand_font_family' => 'Genel Gorunum',
        'global_text_scale' => 'Genel Gorunum',

        'dark_bg_color' => 'Karanlik Mod Renkleri',
        'dark_surface_color' => 'Karanlik Mod Renkleri',
        'dark_surface2_color' => 'Karanlik Mod Renkleri',
        'dark_text_color' => 'Karanlik Mod Renkleri',
        'dark_muted_color' => 'Karanlik Mod Renkleri',
        'dark_border_color' => 'Karanlik Mod Renkleri',
        'dark_primary_color' => 'Karanlik Mod Renkleri',

        'categories_name_color' => 'Kategoriler Sayfasi',
        'categories_stats_color' => 'Kategoriler Sayfasi',
        'categories_description_color' => 'Kategoriler Sayfasi',
        'categories_accent_color' => 'Kategoriler Sayfasi',
        'categories_hover_bg_color' => 'Kategoriler Sayfasi',
        'categories_border_color' => 'Kategoriler Sayfasi',
        'categories_avatar_size' => 'Kategoriler Sayfasi',
        'categories_name_font_size' => 'Kategoriler Sayfasi',
        'categories_stats_font_size' => 'Kategoriler Sayfasi',
        'categories_description_font_size' => 'Kategoriler Sayfasi',

        'font_heading_file' => 'Yazi Tipleri (Tipografi)',
        'font_heading_fallback' => 'Yazi Tipleri (Tipografi)',
        'font_body_file' => 'Yazi Tipleri (Tipografi)',
        'font_body_fallback' => 'Yazi Tipleri (Tipografi)',
        'font_button_file' => 'Yazi Tipleri (Tipografi)',
        'font_button_fallback' => 'Yazi Tipleri (Tipografi)',
        'font_nav_file' => 'Yazi Tipleri (Tipografi)',
        'font_nav_fallback' => 'Yazi Tipleri (Tipografi)',
        'font_code_file' => 'Yazi Tipleri (Tipografi)',
        'font_code_fallback' => 'Yazi Tipleri (Tipografi)',

        'widget_comments_enabled' => 'Kenar Cubugu Widgetlari',
        'widget_comments_count' => 'Kenar Cubugu Widgetlari',
        'widget_tags_enabled' => 'Kenar Cubugu Widgetlari',
        'widget_tags_count' => 'Kenar Cubugu Widgetlari',
        'widget_trending_enabled' => 'Kenar Cubugu Widgetlari',
        'widget_trending_count' => 'Kenar Cubugu Widgetlari',

        'custom_css_file' => 'Ozel CSS',
        'custom_css' => 'Ozel CSS',

        'left_column_html' => 'Sayfaya Ozel Icerikler (HTML)',
        'right_column_html' => 'Sayfaya Ozel Icerikler (HTML)',
        'home_html' => 'Sayfaya Ozel Icerikler (HTML)',
        'messages_html' => 'Sayfaya Ozel Icerikler (HTML)',
        'notifications_html' => 'Sayfaya Ozel Icerikler (HTML)',
        'categories_html' => 'Sayfaya Ozel Icerikler (HTML)',
        'tags_html' => 'Sayfaya Ozel Icerikler (HTML)',
        'profile_html' => 'Sayfaya Ozel Icerikler (HTML)',
        'index_html' => 'Sayfaya Ozel Icerikler (HTML)',
        'post_show_html' => 'Sayfaya Ozel Icerikler (HTML)',
        'header_user_menu_html' => 'Sayfaya Ozel Icerikler (HTML)',

        'header_html' => 'Header Ayarlari (su an pasif)',
        'header_height' => 'Header Ayarlari (su an pasif)',
        'header_padding_x' => 'Header Ayarlari (su an pasif)',
        'header_padding_y' => 'Header Ayarlari (su an pasif)',
        'header_left_width' => 'Header Ayarlari (su an pasif)',
        'header_right_width' => 'Header Ayarlari (su an pasif)',
        'header_bg_color' => 'Header Ayarlari (su an pasif)',
        'header_max_width' => 'Header Ayarlari (su an pasif)',
        'header_search_width' => 'Header Ayarlari (su an pasif)',
        'header_search_border_color' => 'Header Ayarlari (su an pasif)',
        'header_search_input_bg_color' => 'Header Ayarlari (su an pasif)',
        'header_search_dropdown_bg_color' => 'Header Ayarlari (su an pasif)',
        'header_search_text_color' => 'Header Ayarlari (su an pasif)',
        'header_logo_text' => 'Header Ayarlari (su an pasif)',
        'header_logo_image' => 'Header Ayarlari (su an pasif)',
        'header_logo_url' => 'Header Ayarlari (su an pasif)',
        'header_logo_alt' => 'Header Ayarlari (su an pasif)',
        'header_login_label' => 'Header Ayarlari (su an pasif)',
        'header_login_url' => 'Header Ayarlari (su an pasif)',

        'layout_bg_color' => 'Sayfa Duzeni (yalnizca giris/kayit sayfalari)',
        'left_column_bg_color' => 'Sayfa Duzeni (yalnizca giris/kayit sayfalari)',
        'main_column_bg_color' => 'Sayfa Duzeni (yalnizca giris/kayit sayfalari)',
        'right_column_bg_color' => 'Sayfa Duzeni (yalnizca giris/kayit sayfalari)',
        'layout_max_width' => 'Sayfa Duzeni (yalnizca giris/kayit sayfalari)',
        'layout_padding_x' => 'Sayfa Duzeni (yalnizca giris/kayit sayfalari)',
        'layout_padding_y' => 'Sayfa Duzeni (yalnizca giris/kayit sayfalari)',
        'layout_gap' => 'Sayfa Duzeni (yalnizca giris/kayit sayfalari)',
        'layout_left_width' => 'Sayfa Duzeni (yalnizca giris/kayit sayfalari)',
        'layout_main_width' => 'Sayfa Duzeni (yalnizca giris/kayit sayfalari)',
        'layout_right_width' => 'Sayfa Duzeni (yalnizca giris/kayit sayfalari)',
        'layout_max_width_custom' => 'Sayfa Duzeni (yalnizca giris/kayit sayfalari)',
        'layout_padding_x_custom' => 'Sayfa Duzeni (yalnizca giris/kayit sayfalari)',
        'layout_padding_y_custom' => 'Sayfa Duzeni (yalnizca giris/kayit sayfalari)',
        'layout_gap_custom' => 'Sayfa Duzeni (yalnizca giris/kayit sayfalari)',
        'layout_left_width_custom' => 'Sayfa Duzeni (yalnizca giris/kayit sayfalari)',
        'layout_main_width_custom' => 'Sayfa Duzeni (yalnizca giris/kayit sayfalari)',
        'layout_right_width_custom' => 'Sayfa Duzeni (yalnizca giris/kayit sayfalari)',
        'global_shadow' => 'Sayfa Duzeni (yalnizca giris/kayit sayfalari)',
        'header_shadow' => 'Sayfa Duzeni (yalnizca giris/kayit sayfalari)',
    ];

    /** Human-readable Turkish labels for the fields that currently hold real, live values. */
    private const FIELD_LABELS = [
        'brand_background_color' => 'Arka plan rengi',
        'brand_surface_color' => 'Kart / yuzey rengi',
        'brand_button_color' => 'Buton / vurgu rengi',
        'brand_button_hover_color' => 'Buton hover rengi',
        'brand_button_text_color' => 'Buton yazi rengi',
        'brand_text_color' => 'Genel yazi rengi',
        'brand_font_family' => 'Site fontu',
        'global_text_scale' => 'Genel yazi/arayuz boyutu (%)',

        'dark_bg_color' => 'Karanlik mod arka plan rengi',
        'dark_surface_color' => 'Karanlik mod kart/yuzey rengi',
        'dark_surface2_color' => 'Karanlik mod ikincil yuzey rengi',
        'dark_text_color' => 'Karanlik mod yazi rengi',
        'dark_muted_color' => 'Karanlik mod soluk/ikincil yazi rengi',
        'dark_border_color' => 'Karanlik mod kenarlik rengi',
        'dark_primary_color' => 'Karanlik mod vurgu rengi',

        'categories_name_color' => 'Kategori adi rengi',
        'categories_stats_color' => 'Istatistik metni rengi',
        'categories_description_color' => 'Aciklama metni rengi',
        'categories_accent_color' => 'Vurgu rengi',
        'categories_hover_bg_color' => 'Kart hover arka plan rengi',
        'categories_border_color' => 'Ayrac cizgisi rengi',
        'categories_avatar_size' => 'Avatar boyutu (px)',
        'categories_name_font_size' => 'Kategori adi font boyutu (px)',
        'categories_stats_font_size' => 'Istatistik font boyutu (px)',
        'categories_description_font_size' => 'Aciklama font boyutu (px)',

        'font_heading_file' => 'Baslik fontu dosyasi',
        'font_heading_fallback' => 'Baslik yedek font adi',
        'font_body_file' => 'Govde metni fontu dosyasi',
        'font_body_fallback' => 'Govde metni yedek font adi',
        'font_button_file' => 'Buton fontu dosyasi',
        'font_button_fallback' => 'Buton yedek font adi',
        'font_nav_file' => 'Menu fontu dosyasi',
        'font_nav_fallback' => 'Menu yedek font adi',
        'font_code_file' => 'Kod fontu dosyasi',
        'font_code_fallback' => 'Kod yedek font adi',

        'widget_comments_enabled' => "Populer yorumlar widget'i acik mi",
        'widget_comments_count' => 'Populer yorumlar - gosterilecek adet',
        'widget_tags_enabled' => "Etiketler widget'i acik mi",
        'widget_tags_count' => 'Etiketler - gosterilecek adet',
        'widget_trending_enabled' => 'Trend icerik widgeti acik mi',
        'widget_trending_count' => 'Trend icerik - gosterilecek adet',

        'custom_css_file' => 'Ozel CSS dosyasi',
        'custom_css' => 'Ozel CSS kodu',
    ];

    public function getSettings(): ThemeSetting
    {
        return ThemeSetting::current();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Temayi Indir')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->action(fn () => $this->exportTheme()),

            Action::make('import')
                ->label('Tema Yukle')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->schema([
                    FileUpload::make('file')
                        ->label('Tema dosyasi (.json)')
                        ->acceptedFileTypes(['application/json', 'text/plain', '.json'])
                        ->required()
                        ->disk('local')
                        ->directory('theme-imports')
                        ->visibility('private'),
                ])
                ->requiresConfirmation()
                ->modalHeading('Temayi ice aktar')
                ->modalDescription('Bu islem sitenin su anki gorunum ayarlarinin (renkler, fontlar, ozel CSS) tamamen uzerine yazilacak. Devam etmeden once mevcut temani "Temayi Indir" ile yedeklemen onerilir.')
                ->modalSubmitActionLabel('Ice aktar ve uygula')
                ->action(function (array $data): void {
                    $this->importTheme((string) $data['file']);
                }),
        ];
    }

    /**
     * Bundles every ThemeSetting field - including uploaded font/CSS/logo files, embedded
     * as base64 - into a single portable JSON file so the current look can be backed up or
     * moved to another Ografi install. Alongside the flat, canonical "settings" map (which
     * importTheme() reads back), the file also carries a "sections" breakdown that groups
     * every field by the page/area it affects with a human label - so opening the download
     * shows a detailed, page-by-page, color-by-color, font-by-font picture rather than one
     * flat dump.
     */
    private function exportTheme()
    {
        $settings = ThemeSetting::current();
        $data = Arr::only($settings->toArray(), $settings->getFillable());

        $files = [];
        foreach (self::FILE_FIELDS as $field) {
            $path = $data[$field] ?? null;
            unset($data[$field]);

            if (! $path || ! Storage::disk('public')->exists($path)) {
                continue;
            }

            $files[$field] = [
                'filename' => basename((string) $path),
                'mime' => Storage::disk('public')->mimeType($path) ?: 'application/octet-stream',
                'content_base64' => base64_encode(Storage::disk('public')->get($path)),
            ];
        }

        $sections = [];
        foreach ($data as $field => $value) {
            $sections[$this->sectionFor($field)][$field] = [
                'label' => $this->labelFor($field),
                'value' => $value,
            ];
        }
        foreach ($files as $field => $file) {
            $sections[$this->sectionFor($field)][$field] = [
                'label' => $this->labelFor($field),
                'value' => '(yuklu dosya - icerigi asagidaki "files" alaninda base64 olarak saklanir: ' . $file['filename'] . ')',
            ];
        }
        ksort($sections);

        $payload = [
            'ografi_theme_export' => true,
            'version' => 2,
            'exported_at' => now()->toAtomString(),
            'site' => config('app.url'),
            'sections' => $sections,
            'settings' => $data,
            'files' => $files,
        ];

        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $filename = 'ografi-tema-' . now()->format('Y-m-d-His') . '.json';

        return response()->streamDownload(
            fn () => print ($json === false ? '{}' : $json),
            $filename,
            ['Content-Type' => 'application/json'],
        );
    }

    private function sectionFor(string $field): string
    {
        return self::FIELD_SECTIONS[$field] ?? 'Diger Ayarlar';
    }

    private function labelFor(string $field): string
    {
        return self::FIELD_LABELS[$field] ?? Str::of($field)->replace('_', ' ')->ucfirst()->toString();
    }

    /**
     * Reads only the flat "settings" map (present in every export version) so v1 exports
     * downloaded before the "sections" breakdown was added still import correctly.
     */
    private function importTheme(string $uploadedPath): void
    {
        if ($uploadedPath === '' || ! Storage::disk('local')->exists($uploadedPath)) {
            $this->notifyImportFailure('Yuklenen dosya bulunamadi.');

            return;
        }

        $raw = Storage::disk('local')->get($uploadedPath);
        Storage::disk('local')->delete($uploadedPath);

        $payload = json_decode((string) $raw, true);

        if (
            ! is_array($payload)
            || ($payload['ografi_theme_export'] ?? false) !== true
            || ! is_array($payload['settings'] ?? null)
        ) {
            $this->notifyImportFailure('Dosya gecerli bir Ografi tema paketi degil.');

            return;
        }

        $settings = ThemeSetting::current();
        $allowedFields = $settings->getFillable();

        $incoming = Arr::except(Arr::only($payload['settings'], $allowedFields), self::FILE_FIELDS);
        // Defensive: a hand-edited/corrupted export could carry an array/object for what
        // should be a plain string, number or boolean column - drop anything that isn't.
        $incoming = array_filter($incoming, fn ($value) => is_scalar($value) || $value === null);

        foreach ((array) ($payload['files'] ?? []) as $field => $file) {
            if (! in_array($field, self::FILE_FIELDS, true) || ! in_array($field, $allowedFields, true)) {
                continue;
            }

            if (! is_array($file) || ! is_string($file['content_base64'] ?? null) || ! is_string($file['filename'] ?? null)) {
                continue;
            }

            $decoded = base64_decode($file['content_base64'], true);
            if ($decoded === false) {
                continue;
            }

            $extension = pathinfo($file['filename'], PATHINFO_EXTENSION) ?: 'bin';
            $directory = match ($field) {
                'custom_css_file' => 'custom-theme',
                'header_logo_image' => 'theme',
                default => 'fonts',
            };
            $storedPath = $directory . '/' . Str::random(20) . '.' . $extension;

            Storage::disk('public')->put($storedPath, $decoded);
            $incoming[$field] = $storedPath;
        }

        $settings->update($incoming);
        Cache::forget('theme-appearance-css');

        Notification::make()
            ->title('Tema ice aktarildi')
            ->body('Yeni gorunum ayarlari siteye uygulandi.')
            ->success()
            ->send();
    }

    private function notifyImportFailure(string $message): void
    {
        Notification::make()
            ->title('Tema yuklenemedi')
            ->body($message)
            ->danger()
            ->send();
    }
}
