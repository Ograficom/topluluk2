<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class SeoSettings extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static string|\UnitEnum|null $navigationGroup = 'Ayarlar';
    protected static ?string $navigationLabel = 'SEO ayarları';
    protected static ?string $title = 'Site SEO yönetimi';
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlass;
    protected static ?int $navigationSort = 2;
    protected string $view = 'filament.pages.seo-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = SiteSetting::current();
        $this->form->fill([
            'seo_default_title' => $settings?->seo_default_title,
            'seo_default_description' => $settings?->seo_default_description,
            'seo_default_keywords' => $settings?->seo_default_keywords,
            'seo_og_image' => $settings?->seo_og_image,
            'home_seo_title' => $settings?->home_seo_title,
            'home_seo_description' => $settings?->home_seo_description,
            'home_seo_keywords' => $settings?->home_seo_keywords,
            'home_seo_index' => $settings?->home_seo_index ?? true,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Ana sayfa SEO')
                ->description('Google ve sosyal ağlarda OgraFi ana sayfası için görünecek bilgiler.')
                ->schema([
                    TextInput::make('home_seo_title')->label('Ana sayfa SEO başlığı')->maxLength(255)->helperText('Yaklaşık 50–60 karakter önerilir.'),
                    Textarea::make('home_seo_description')->label('Ana sayfa meta açıklaması')->rows(3)->maxLength(500)->helperText('Yaklaşık 140–160 karakter, doğal ve özgün bir açıklama yazın.')->columnSpanFull(),
                    TextInput::make('home_seo_keywords')->label('Ana sayfa anahtar kelimeleri')->maxLength(1000)->helperText('Virgülle ayırın. Anahtar kelimeler Google sıralamasında doğrudan belirleyici değildir.')->columnSpanFull(),
                    Toggle::make('home_seo_index')->label('Ana sayfayı arama motorlarına indekslet')->default(true),
                ])->columns(2),
            Section::make('Genel SEO varsayılanları')
                ->description('Özel SEO bilgisi girilmemiş sayfalarda kullanılacak varsayılan değerler.')
                ->schema([
                    TextInput::make('seo_default_title')->label('Varsayılan SEO başlığı')->maxLength(255),
                    Textarea::make('seo_default_description')->label('Varsayılan meta açıklaması')->rows(3)->maxLength(500)->columnSpanFull(),
                    TextInput::make('seo_default_keywords')->label('Varsayılan anahtar kelimeler')->maxLength(1000)->columnSpanFull(),
                    FileUpload::make('seo_og_image')
                        ->label('Varsayılan paylaşım görseli')
                        ->image()
                        ->imageEditor()
                        ->imageCropAspectRatio('1200:630')
                        ->imageResizeMode('cover')
                        ->imageResizeTargetWidth(1200)
                        ->imageResizeTargetHeight(630)
                        ->disk('public')
                        ->directory('seo')
                        ->visibility('public')
                        ->maxSize(10240)
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->helperText('Sosyal medya paylaşım görseli. 1200 × 630 px oranında kırpılır ve yeniden boyutlandırılır. JPG, PNG veya WebP; en fazla 10 MB.')
                        ->columnSpanFull(),
                ])->columns(2),
        ])->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $settings = SiteSetting::current() ?? new SiteSetting();
        $settings->id = 1;
        $settings->fill([
            'seo_default_title' => filled($data['seo_default_title'] ?? null) ? trim($data['seo_default_title']) : null,
            'seo_default_description' => filled($data['seo_default_description'] ?? null) ? trim($data['seo_default_description']) : null,
            'seo_default_keywords' => filled($data['seo_default_keywords'] ?? null) ? trim($data['seo_default_keywords']) : null,
            'seo_og_image' => filled($data['seo_og_image'] ?? null) ? $data['seo_og_image'] : null,
            'home_seo_title' => filled($data['home_seo_title'] ?? null) ? trim($data['home_seo_title']) : null,
            'home_seo_description' => filled($data['home_seo_description'] ?? null) ? trim($data['home_seo_description']) : null,
            'home_seo_keywords' => filled($data['home_seo_keywords'] ?? null) ? trim($data['home_seo_keywords']) : null,
            'home_seo_index' => (bool) ($data['home_seo_index'] ?? false),
        ]);
        $settings->save();

        Notification::make()->title('SEO ayarları kaydedildi.')->success()->send();
    }
}
