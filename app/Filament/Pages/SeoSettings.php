<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use App\Models\SeoPageSetting;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class SeoSettings extends Page
{
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
            'pages' => SeoPageSetting::query()->orderBy('route_name')->get()->map(fn (SeoPageSetting $page) => $page->only(['route_name', 'title', 'description', 'keywords', 'canonical_url', 'og_image', 'robots_index']))->all(),
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
            Section::make('Sayfa bazlı SEO ayarları')
                ->description('Akış dışındaki sabit ve liste sayfalarını Laravel route adına göre ayrı ayrı yönetin. Örnek: channels.index, tags.index, members.index, contacts, stats.index. Yazı, kanal, statik sayfa ve profil SEO alanları kendi düzenleme ekranlarındadır.')
                ->schema([
                    Repeater::make('pages')
                        ->label('SEO tanımlı sayfalar')
                        ->schema([
                            TextInput::make('route_name')->label('Laravel route adı')->required()->maxLength(120)->placeholder('ör. tags.index'),
                            TextInput::make('title')->label('SEO başlığı')->maxLength(255),
                            Textarea::make('description')->label('Meta açıklaması')->rows(2)->maxLength(500)->columnSpanFull(),
                            TextInput::make('keywords')->label('Anahtar kelimeler')->maxLength(2000)->columnSpanFull(),
                            TextInput::make('canonical_url')->label('Canonical URL')->url()->maxLength(2048)->columnSpanFull(),
                            TextInput::make('og_image')->label('Sosyal paylaşım görseli URL')->url()->maxLength(2048)->columnSpanFull(),
                            Toggle::make('robots_index')->label('İndekslemeye izin ver')->default(true),
                        ])->columns(2)->defaultItems(0)->addActionLabel('Sayfa SEO ayarı ekle')->columnSpanFull(),
                ]),
            Section::make('Genel SEO varsayılanları')
                ->description('Özel SEO bilgisi girilmemiş sayfalarda kullanılacak varsayılan değerler.')
                ->schema([
                    TextInput::make('seo_default_title')->label('Varsayılan SEO başlığı')->maxLength(255),
                    Textarea::make('seo_default_description')->label('Varsayılan meta açıklaması')->rows(3)->maxLength(500)->columnSpanFull(),
                    TextInput::make('seo_default_keywords')->label('Varsayılan anahtar kelimeler')->maxLength(1000)->columnSpanFull(),
                    TextInput::make('seo_og_image')->label('Varsayılan paylaşım görseli URL')->url()->maxLength(2048)->helperText('Open Graph / sosyal medya paylaşımlarında kullanılacak mutlak görsel adresi.')->columnSpanFull(),
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
            'seo_og_image' => filled($data['seo_og_image'] ?? null) ? trim($data['seo_og_image']) : null,
            'home_seo_title' => filled($data['home_seo_title'] ?? null) ? trim($data['home_seo_title']) : null,
            'home_seo_description' => filled($data['home_seo_description'] ?? null) ? trim($data['home_seo_description']) : null,
            'home_seo_keywords' => filled($data['home_seo_keywords'] ?? null) ? trim($data['home_seo_keywords']) : null,
            'home_seo_index' => (bool) ($data['home_seo_index'] ?? false),
        ]);
        $settings->save();

        $pages = collect($data['pages'] ?? [])
            ->filter(fn (array $page): bool => filled($page['route_name'] ?? null))
            ->unique('route_name')
            ->values();
        SeoPageSetting::query()->whereNotIn('route_name', $pages->pluck('route_name'))->delete();
        foreach ($pages as $page) {
            SeoPageSetting::query()->updateOrCreate(
                ['route_name' => trim($page['route_name'])],
                [
                    'title' => filled($page['title'] ?? null) ? trim($page['title']) : null,
                    'description' => filled($page['description'] ?? null) ? trim($page['description']) : null,
                    'keywords' => filled($page['keywords'] ?? null) ? trim($page['keywords']) : null,
                    'canonical_url' => filled($page['canonical_url'] ?? null) ? trim($page['canonical_url']) : null,
                    'og_image' => filled($page['og_image'] ?? null) ? trim($page['og_image']) : null,
                    'robots_index' => (bool) ($page['robots_index'] ?? false),
                ],
            );
        }

        Notification::make()->title('SEO ayarları kaydedildi.')->success()->send();
    }
}
