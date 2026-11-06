<?php

namespace App\Filament\Pages;

use App\Models\RssSetting;
use App\Services\RssFeedService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class RssManager extends Page
{
    protected static ?string $navigationLabel = 'RSS yönetimi';
    protected static ?string $title = 'RSS akış yönetimi';
    protected static string|\UnitEnum|null $navigationGroup = 'Site ayarları';
    protected static ?int $navigationSort = 111;
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected string $view = 'filament.pages.rss-manager';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = RssSetting::current();

        $this->form->fill($settings->only([
            'enabled',
            'latest_enabled',
            'author_feeds_enabled',
            'channel_feeds_enabled',
            'tag_feeds_enabled',
            'item_limit',
            'content_mode',
            'cache_minutes',
        ]));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Yayınlanacak akışlar')
                    ->description('Her akış türünü bağımsız olarak açıp kapatabilirsiniz.')
                    ->icon(Heroicon::OutlinedRss)
                    ->schema([
                        Toggle::make('enabled')
                            ->label('RSS sistemini etkinleştir')
                            ->helperText('Kapalı olduğunda tüm RSS adresleri devre dışı kalır.')
                            ->default(true),
                        Toggle::make('latest_enabled')
                            ->label('Son yazılar akışı')
                            ->helperText('Sitenin en yeni yazılarını yayınlar.')
                            ->default(true),
                        Toggle::make('author_feeds_enabled')
                            ->label('Yazar akışları')
                            ->helperText('Her yazarın kendine ait RSS adresi olur.')
                            ->default(true),
                        Toggle::make('channel_feeds_enabled')
                            ->label('Kanal akışları')
                            ->helperText('Her aktif kanal için ayrı akış yayınlar.')
                            ->default(true),
                        Toggle::make('tag_feeds_enabled')
                            ->label('Etiket akışları')
                            ->helperText('Her etiket için ayrı akış yayınlar.')
                            ->default(true),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ]),

                Section::make('İçerik ve performans')
                    ->description('RSS içerik uzunluğunu ve önbellek davranışını ayarlayın.')
                    ->icon(Heroicon::OutlinedAdjustmentsHorizontal)
                    ->schema([
                        TextInput::make('item_limit')
                            ->label('Akış başına yazı sayısı')
                            ->helperText('Her RSS adresinde gösterilecek en fazla yazı.')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(100)
                            ->required()
                            ->default(20),
                        Select::make('content_mode')
                            ->label('İçerik biçimi')
                            ->helperText('Özet daha kısa akış üretir; tam metin yazının içeriğini ekler.')
                            ->options([
                                'excerpt' => 'Yalnızca özet',
                                'full' => 'Tam metin',
                            ])
                            ->required()
                            ->default('excerpt'),
                        TextInput::make('cache_minutes')
                            ->label('Önbellek süresi (dakika)')
                            ->helperText('Düşük değer daha sık güncelleme, yüksek değer daha az sorgu demektir.')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(1440)
                            ->required()
                            ->default(10)
                            ->columnSpanFull(),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        RssSetting::current()->update([
            'enabled' => (bool) ($data['enabled'] ?? false),
            'latest_enabled' => (bool) ($data['latest_enabled'] ?? false),
            'author_feeds_enabled' => (bool) ($data['author_feeds_enabled'] ?? false),
            'channel_feeds_enabled' => (bool) ($data['channel_feeds_enabled'] ?? false),
            'tag_feeds_enabled' => (bool) ($data['tag_feeds_enabled'] ?? false),
            'item_limit' => (int) $data['item_limit'],
            'content_mode' => $data['content_mode'],
            'cache_minutes' => (int) $data['cache_minutes'],
        ]);

        app(RssFeedService::class)->invalidate();

        Notification::make()
            ->title('RSS ayarları kaydedildi.')
            ->success()
            ->send();
    }

    public function feedUrls(): array
    {
        return [
            ['label' => 'Genel RSS', 'url' => url('/rss.xml')],
            ['label' => 'Son yazılar', 'url' => url('/feed/latest.xml')],
            ['label' => 'Yazar örneği', 'url' => url('/feed/authors/{username}.xml')],
            ['label' => 'Kanal örneği', 'url' => url('/feed/channels/{slug}.xml')],
            ['label' => 'Etiket örneği', 'url' => url('/feed/tags/{slug}.xml')],
        ];
    }
}
