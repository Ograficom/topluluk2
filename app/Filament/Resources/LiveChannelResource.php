<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LiveChannelResource\Pages;
use App\Models\LiveChannel;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LiveChannelResource extends Resource
{
    protected static ?string $model = LiveChannel::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Video';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-tv';

    protected static ?string $navigationLabel = 'Canlı TV Kanalları';

    protected static ?string $modelLabel = 'Canlı kanal';

    protected static ?string $pluralModelLabel = 'Canlı TV Kanalları';

    protected static bool $isGloballySearchable = true;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Grid::make(2)->schema([
                TextInput::make('name')
                    ->label('Kanal adı')
                    ->required()
                    ->maxLength(255),
                Select::make('category')
                    ->label('Tür')
                    ->options(self::categoryOptions())
                    ->searchable()
                    ->required()
                    ->default('Genel'),
            ]),

            TextInput::make('stream_url')
                ->label('M3U8 / yayın adresi')
                ->url()
                ->required()
                ->maxLength(2048)
                ->unique(table: 'live_channels', column: 'stream_url', ignoreRecord: true)
                ->columnSpanFull(),

            FileUpload::make('featured_image')
                ->label('Öne çıkan görsel')
                ->image()
                ->disk('public')
                ->directory('live-channels')
                ->visibility('public')
                ->imageEditor()
                ->maxSize(8192)
                ->helperText('Video sayfasındaki kanal kartında gösterilir. 16:9 görsel önerilir.')
                ->columnSpanFull(),

            Grid::make(2)->schema([
                TextInput::make('sort_order')
                    ->label('Sıralama')
                    ->numeric()
                    ->minValue(0)
                    ->default(0),
                Toggle::make('is_active')
                    ->label('Yayında göster')
                    ->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('featured_image')
                    ->label('Görsel')
                    ->disk('public')
                    ->square(),
                TextColumn::make('name')
                    ->label('Kanal')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category')
                    ->label('Tür')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('stream_url')
                    ->label('Yayın adresi')
                    ->limit(45)
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sort_order')
                    ->label('Sıra')
                    ->sortable(),
                TextColumn::make('is_active')
                    ->label('Durum')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Kapalı')
                    ->badge()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Güncellendi')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Tür')
                    ->options(self::categoryOptions()),
                SelectFilter::make('is_active')
                    ->label('Durum')
                    ->options([
                        '1' => 'Aktif',
                        '0' => 'Kapalı',
                    ]),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLiveChannels::route('/'),
            'create' => Pages\CreateLiveChannel::route('/create'),
            'edit' => Pages\EditLiveChannel::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'category', 'stream_url'];
    }

    public static function categoryOptions(): array
    {
        return [
            'Belgesel' => 'Belgesel',
            'Haber' => 'Haber',
            'Spor' => 'Spor',
            'Eğlence' => 'Eğlence',
            'Müzik' => 'Müzik',
            'Çocuk' => 'Çocuk',
            'Sinema' => 'Sinema',
            'Yerel' => 'Yerel',
            'Genel' => 'Genel',
        ];
    }
}
