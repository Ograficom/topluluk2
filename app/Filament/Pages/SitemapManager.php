<?php

namespace App\Filament\Pages;

use App\Models\SitemapSetting;
use App\Services\SitemapService;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class SitemapManager extends Page
{
    protected static ?string $navigationLabel = 'Sitemap yönetimi';

    protected static ?string $title = 'Sitemap oluşturucu';

    protected static ?string $navigationGroup = 'Site ayarları';

    protected static ?int $navigationSort = 110;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected string $view = 'filament.pages.sitemap-manager';

    public ?array $data = [];

    public int $urlCount = 0;

    public ?string $lastGeneratedAt = null;

    public function mount(): void
    {
        $settings = SitemapSetting::current();

        $this->form->fill([
            'enabled' => $settings->enabled,
            'base_url' => $settings->base_url ?: config('app.url'),
            'include' => $settings->include ?? ['posts', 'channels', 'tags', 'pages'],
        ]);

        $this->syncStatus();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Sitemap ayarları')
                    ->description('Arama motorlarına açık içeriklerin XML sitemap dosyasını yönetin.')
                    ->schema([
                        Toggle::make('enabled')
                            ->label('Sitemap yayınını etkinleştir')
                            ->default(true),
                        TextInput::make('base_url')
                            ->label('Site adresi')
                            ->url()
                            ->required()
                            ->maxLength(2048)
                            ->helperText('Örnek: https://ografi.com'),
                        CheckboxList::make('include')
                            ->label('Sitemap içine eklenecek içerikler')
                            ->options([
                                'posts' => 'Yayınlanmış yazılar',
                                'channels' => 'Aktif kanallar',
                                'tags' => 'Yayınlanmış yazılarda kullanılan etiketler',
                                'pages' => 'Ana sayfa ve sabit sayfalar',
                            ])
                            ->columns(2)
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $settings = SitemapSetting::current();

        $settings->fill([
            'enabled' => (bool) ($data['enabled'] ?? false),
            'base_url' => rtrim($data['base_url'], '/'),
            'include' => array_values($data['include'] ?? []),
        ])->save();

        app(SitemapService::class)->invalidate();

        Notification::make()
            ->title('Sitemap ayarları kaydedildi.')
            ->success()
            ->send();

        $this->syncStatus();
    }

    public function rebuild(): void
    {
        $settings = SitemapSetting::current();

        if (! $settings->enabled) {
            Notification::make()
                ->title('Sitemap yayını kapalı.')
                ->body('Önce sitemap yayınını etkinleştirin.')
                ->warning()
                ->send();

            return;
        }

        try {
            $result = app(SitemapService::class)->rebuild();
            $this->syncStatus();

            Notification::make()
                ->title('Sitemap yeniden oluşturuldu.')
                ->body(number_format($result['url_count']).' URL sitemap dosyasına eklendi.')
                ->success()
                ->send();
        } catch (\Throwable $exception) {
            report($exception);

            Notification::make()
                ->title('Sitemap oluşturulamadı.')
                ->body('Hata kaydedildi. Uygulama günlüklerini kontrol edin.')
                ->danger()
                ->send();
        }
    }

    private function syncStatus(): void
    {
        $settings = SitemapSetting::current();
        $this->urlCount = (int) $settings->url_count;
        $this->lastGeneratedAt = $settings->last_generated_at?->format('d.m.Y H:i');
    }

    public function sitemapUrl(): string
    {
        return url('/sitemap.xml');
    }
}
