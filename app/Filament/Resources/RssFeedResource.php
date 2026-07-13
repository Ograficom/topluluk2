<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RssFeedResource\Pages;
use App\Filament\Resources\RssFeedResource\RelationManagers\ItemsRelationManager;
use App\Models\RssFeed;
use App\Services\Rss\RssSyncService;
use Filament\Actions;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RssFeedResource extends Resource
{
    protected static ?string $model = RssFeed::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Blog';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rss';

    protected static ?string $navigationLabel = 'RSS';

    protected static ?string $modelLabel = 'RSS Feed';

    protected static ?string $pluralModelLabel = 'RSS Feeds';

    protected static bool $isGloballySearchable = false;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Grid::make(2)->schema([
                TextInput::make('name')
                    ->label('Ad')
                    ->maxLength(255)
                    ->placeholder('Orn: Webtekno'),
                TextInput::make('url')
                    ->label('RSS URL')
                    ->required()
                    ->url()
                    ->maxLength(2048)
                    ->columnSpan(1),
            ]),
            Grid::make(3)->schema([
                Toggle::make('is_enabled')->label('Aktif')->default(true),
                Toggle::make('import_as_posts')->label('Gonderi olarak ice aktar')->default(true),
                Toggle::make('auto_publish')->label('Otomatik yayinla')->default(false),
            ]),
            Toggle::make('fetch_dom_content')
                ->label('DOM cekmeyi ac')
                ->helperText('Kapaliysa haber sayfasina girilmez; icerik RSS verisinden okunur.')
                ->default(true),
            Toggle::make('update_existing_posts')
                ->label('Mevcut postlari guncelle')
                ->default(true),
            Grid::make(2)->schema([
                Toggle::make('ai_rewrite_enabled')
                    ->label('Yapay zeka ile yeniden yaz')
                    ->helperText('Yerel Ollama kullanir. Basarisiz olursa kaynak metni kopyalayarak post olusturmaz.')
                    ->default(false),
                TextInput::make('ai_model')
                    ->label('Ollama modeli')
                    ->default(fn () => config('services.ollama.model', 'gpt-oss:20b'))
                    ->placeholder('gpt-oss:20b')
                    ->helperText('Bos birakilirsa .env icindeki OLLAMA_CLOUD_MODEL, yoksa OLLAMA_MODEL kullanilir.')
                    ->maxLength(255),
            ]),
            Grid::make(2)->schema([
                Select::make('default_category_id')
                    ->label('Varsayilan kategori')
                    ->relationship('defaultCategory', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Select::make('default_author_id')
                    ->label('Varsayilan yazar')
                    ->relationship('defaultAuthor', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),
            ]),
            Grid::make(2)->schema([
                Placeholder::make('last_success_at')
                    ->label('Son basarili sync')
                    ->content(fn (?RssFeed $record) => $record?->last_success_at?->toDateTimeString() ?? '—'),
                Placeholder::make('last_error')
                    ->label('Son hata')
                    ->content(fn (?RssFeed $record) => $record?->last_error ?: '—'),
            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Ad')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('url')
                    ->label('URL')
                    ->limit(60)
                    ->searchable(),
                IconColumn::make('is_enabled')
                    ->label('Aktif')
                    ->boolean(),
                IconColumn::make('fetch_dom_content')
                    ->label('DOM')
                    ->boolean(),
                IconColumn::make('ai_rewrite_enabled')
                    ->label('AI')
                    ->boolean(),
                TextColumn::make('ai_model')
                    ->label('Model')
                    ->formatStateUsing(fn (?string $state) => filled($state) ? $state : config('services.ollama.model', 'gpt-oss:20b'))
                    ->badge()
                    ->toggleable(),
                TextColumn::make('last_success_at')
                    ->label('Son sync')
                    ->since()
                    ->sortable(),
                TextColumn::make('last_error')
                    ->label('Hata')
                    ->limit(40)
                    ->toggleable(),
            ])
            ->headerActions([
                Actions\CreateAction::make(),
                Actions\Action::make('sync_all')
                    ->label('Sync All')
                    ->icon('heroicon-o-arrow-path')
                    ->action(function () {
                        $summary = app(RssSyncService::class)->syncAllEnabled();
                        $hasErrors = (int) ($summary['errors'] ?? 0) > 0;

                        \Filament\Notifications\Notification::make()
                            ->title($hasErrors ? 'RSS sync tamamlandi (hata var)' : 'RSS sync tamamlandi')
                            ->body("feeds={$summary['feeds']} items_new={$summary['items_new']} items_updated={$summary['items_updated']} posts_created={$summary['posts_created']} posts_updated={$summary['posts_updated']} errors={$summary['errors']}")
                            ->status($hasErrors ? 'warning' : 'success')
                            ->send();
                    }),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\Action::make('sync_now')
                    ->label('Sync')
                    ->icon('heroicon-o-arrow-path')
                    ->action(function (RssFeed $record) {
                        $result = app(RssSyncService::class)->syncFeed($record, true);
                        $hasError = (bool) $result['error'];

                        \Filament\Notifications\Notification::make()
                            ->title($hasError ? 'RSS sync hata' : 'RSS sync OK')
                            ->body($hasError
                                ? $result['error']
                                : "items_new={$result['items_new']} items_updated={$result['items_updated']} posts_created={$result['posts_created']} posts_updated={$result['posts_updated']}")
                            ->status($hasError ? 'danger' : 'success')
                            ->send();
                    }),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRssFeeds::route('/'),
            'create' => Pages\CreateRssFeed::route('/create'),
            'edit' => Pages\EditRssFeed::route('/{record}/edit'),
        ];
    }
}












