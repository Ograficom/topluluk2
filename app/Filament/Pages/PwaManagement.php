<?php

namespace App\\Filament\\Pages;

use App\\Models\\SiteSetting;
use Filament\\Forms\\Components\\FileUpload;
use Filament\\Forms\\Components\\Select;
use Filament\\Forms\\Components\\Textarea;
use Filament\\Forms\\Components\\TextInput;
use Filament\\Forms\\Components\\Toggle;
use Filament\\Notifications\\Notification;
use Filament\\Pages\\Page;
use Filament\\Schemas\\Components\\Section;
use Filament\\Schemas\\Schema;
use Filament\\Support\\Icons\\Heroicon;

class PwaManagement extends Page
{
    protected static bool $shouldRegisterNavigation = false;
    protected static string|\\UnitEnum|null $navigationGroup = 'Ayarlar';
    protected static ?string $navigationLabel = 'PWA yönetimi';
    protected static ?string $title = 'PWA uygulama yönetimi';
    protected static string|\\BackedEnum|null $navigationIcon = Heroicon::OutlinedDevicePhoneMobile;
    protected static ?int $navigationSort = 7;
    protected string $view = 'filament.pages.pwa-management';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = SiteSetting::current();

        $this->form->fill([
            'pwa_enabled' => $settings?->pwa_enabled ?? true,
            'pwa_name' => $settings?->pwa_name ?? 'OgraFi',
            'pwa_short_name' => $settings?->pwa_short_name ?? 'OgraFi',
            'pwa_description' => $settings?->pwa_description ?? 'OgraFi topluluk uygulaması',
            'pwa_start_url' => $settings?->pwa_start_url ?? '/',
            'pwa_display' => $settings?->pwa_display ?? 'standalone',
            'pwa_orientation' => $settings?->pwa_orientation ?? 'any',
            'pwa_theme_color' => $settings?->pwa_theme_color ?? '#2563eb',
            'pwa_background_color' => $settings?->pwa_background_color ?? '#f7f9fd',
            'pwa_icon_192_path' => $settings?->pwa_icon_192_path,
            'pwa_icon_512_path' => $settings?->pwa_icon_512_path,
            'pwa_maskable_icon_512_path' => $settings?->pwa_maskable_icon_512_path,
            'pwa_offline_title' => $settings?->pwa_offline_title ?? 'İnternet bağlantısı yok',
            'pwa_offline_message' => $settings?->pwa_offline_message ?? 'Bağlantın yeniden geldiğinde sayfayı yenileyebilirsin.',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Uygulama durumu ve kimliği')
                ->description('PWA özelliğini açıp kapatabilir, uygulamanın cihazlarda nasıl görüneceğini belirleyebilirsin.')
                ->schema([
                    Toggle::make('pwa_enabled')->label('PWA özelliği aktif')->default(true),
                    TextInput::make('pwa_name')->label('Uygulama adı')->required()->maxLength(80),
                    TextInput::make('pwa_short_name')->label('Kısa uygulama adı')->required()->maxLength(24)->helperText('Ana ekrandaki ikonun altında görünür.'),
                    Textarea::make('pwa_description')->label('Uygulama açıklaması')->rows(2)->maxLength(250)->columnSpanFull(),
                    TextInput::make('pwa_start_url')->label('Açılış adresi')->required()->default('/')->maxLength(255)->regex('/^\\/(?!\\/).*/')->helperText('Yalnızca site içi yol kullan. Örnek: / veya /last-posts'),
                    Select::make('pwa_display')->label('Açılış biçimi')->options([
                        'standalone' => 'Bağımsız uygulama (önerilen)',
                        'minimal-ui' => 'Minimal tarayıcı kontrolleri',
                        'browser' => 'Normal tarayıcı',
                        'fullscreen' => 'Tam ekran',
                    ])->required()->default('standalone'),
                    Select::make('pwa_orientation')->label('Ekran yönü')->options([
                        'any' => 'Otomatik',
                        'portrait' => 'Dikey',
                        'landscape' => 'Yatay',
                    ])->required()->default('any'),
                ])->columns(2),
            Section::make('Uygulama renkleri')
                ->description('Tarayıcı üst çubuğu ve uygulama açılış ekranında kullanılacak renkler.')
                ->schema([
                    TextInput::make('pwa_theme_color')->label('Tema rengi')->required()->regex('/^#[0-9A-Fa-f]{6}$/')->default('#2563eb')->helperText('HEX formatı: #2563eb'),
                    TextInput::make('pwa_background_color')->label('Arka plan rengi')->required()->regex('/^#[0-9A-Fa-f]{6}$/')->default('#f7f9fd')->helperText('HEX formatı: #f7f9fd'),
                ])->columns(2),
            Section::make('Uygulama ikonları')
                ->description('Yüklenen görseller kare PNG olmalı. Alanlar yüklenen görseli belirtilen boyuta dönüştürür.')
                ->schema([
                    FileUpload::make('pwa_icon_192_path')->label('192 × 192 ikon')->image()->disk('public')->directory('pwa/icons')->visibility('public')->acceptedFileTypes(['image/png','image/jpeg','image/webp'])->imageResizeMode('cover')->imageResizeTargetWidth(192)->imageResizeTargetHeight(192)->maxSize(5120)->helperText('Android ve küçük ekranlar için.'),
                    FileUpload::make('pwa_icon_512_path')->label('512 × 512 ikon')->image()->disk('public')->directory('pwa/icons')->visibility('public')->acceptedFileTypes(['image/png','image/jpeg','image/webp'])->imageResizeMode('cover')->imageResizeTargetWidth(512)->imageResizeTargetHeight(512)->maxSize(5120)->helperText('Chrome yükleme deneyimi için gerekli büyük ikon.'),
                    FileUpload::make('pwa_maskable_icon_512_path')->label('512 × 512 maskable ikon (isteğe bağlı)')->image()->disk('public')->directory('pwa/icons')->visibility('public')->acceptedFileTypes(['image/png','image/jpeg','image/webp'])->imageResizeMode('cover')->imageResizeTargetWidth(512)->imageResizeTargetHeight(512)->maxSize(5120)->helperText('Maskable ikon yüklenmezse normal 512 ikon kullanılır.'),
                ])->columns(3),
            Section::make('Çevrimdışı sayfa')
                ->description('İnternet olmadığında ziyaretçilere gösterilecek metin.')
                ->schema([
                    TextInput::make('pwa_offline_title')->label('Başlık')->required()->maxLength(100),
                    Textarea::make('pwa_offline_message')->label('Açıklama')->required()->rows(3)->maxLength(500)->columnSpanFull(),
                ])->columns(2),
        ])->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $settings = SiteSetting::current() ?? new SiteSetting();
        $settings->id = 1;
        $settings->fill([
            'pwa_enabled' => (bool) ($data['pwa_enabled'] ?? false),
            'pwa_name' => trim($data['pwa_name']),
            'pwa_short_name' => trim($data['pwa_short_name']),
            'pwa_description' => filled($data['pwa_description'] ?? null) ? trim($data['pwa_description']) : null,
            'pwa_start_url' => trim($data['pwa_start_url']),
            'pwa_display' => $data['pwa_display'],
            'pwa_orientation' => $data['pwa_orientation'],
            'pwa_theme_color' => $data['pwa_theme_color'],
            'pwa_background_color' => $data['pwa_background_color'],
            'pwa_icon_192_path' => $data['pwa_icon_192_path'] ?? null,
            'pwa_icon_512_path' => $data['pwa_icon_512_path'] ?? null,
            'pwa_maskable_icon_512_path' => $data['pwa_maskable_icon_512_path'] ?? null,
            'pwa_offline_title' => trim($data['pwa_offline_title']),
            'pwa_offline_message' => trim($data['pwa_offline_message']),
        ]);
        $settings->save();

        Notification::make()->title('PWA ayarları kaydedildi.')->success()->send();
    }
}
