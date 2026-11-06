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
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedRss;

    protected string $view = 'filament.pages.rss-manager';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = RssSetting::current();
        $this->form->fill($settings->only([
            'enabled', 'latest_enabled', 'author_feeds_enabled',
            'channel_feeds_enabled', 'tag_feeds_enabled',
            'item_limit', 'content_mode', 'cache_minutes',
        ]));
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('RSS yayın ayarları')
                ->description('OgraFi genel, yazar, kanal ve etiket RSS akışlarını yönetin.')
                ->schema([
                    Toggle::make('enabled')->label('RSS sistemini etkinleştir')->default(true),
                    Toggle::make('latest_enabled')->label('Son yazılar akışını etkinleştir')->default(true),
                    Toggle::make('author_feeds_enabled')->label('Yazar akışlarını etkinleştir')->default(true),
                    Toggle::make('channel_feeds_enabled')->label('Kanal akışlarını etkinleştir')->default(true),
                    Toggle::make('tag_feeds_enabled')->label('Etiket akışlarını etkinleştir')->default(true),
                    TextInput::make('item_limit')->label('Akış başına yazı sayısı')->numeric()->minValue(1)->maxValue(100)->required(),
                    Select::make('content_mode')->label('İçerik biçimi')->options([
                        'excerpt' => 'Özet',
                        'full' => 'Tam metin (HTML etiketleri temizlenir)',
                    ])->required(),
                    TextInput::make('cache_minutes')->label('Önbellek süresi (dakika)')->numeric()->minValue(1)->maxValue(1440)->required(),
                ])->columns(2),
        ])->statePath('data');
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

        Notification::make()->title('RSS ayarları kaydedildi.')->success()->send();
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
