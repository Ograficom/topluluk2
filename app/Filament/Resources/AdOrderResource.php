<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdOrderResource\Pages;
use App\Models\AdOrder;
use Filament\Actions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AdOrderResource extends Resource
{
    protected static ?string $model = AdOrder::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Icerik';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationLabel = 'Reklam Siparisleri';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Reklam Siparisi';

    protected static ?string $pluralModelLabel = 'Reklam Siparisleri';

    protected static bool $isGloballySearchable = false;

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    private static function placementOptions(): array
    {
        return [
            'sidebar_top' => 'Sag sidebar ust',
            'sidebar_story' => 'Sag sidebar orta',
            'feed_inline' => 'Akis ici',
            'mobile_inline' => 'Mobil akis',
        ];
    }

    private static function statusOptions(): array
    {
        return [
            'pending_payment' => 'Odeme bekliyor',
            'paid' => 'Odendi',
            'active' => 'Aktif',
            'paused' => 'Duraklatildi',
            'expired' => 'Suresi doldu',
            'rejected' => 'Reddedildi',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Grid::make(2)->schema([
                Select::make('user_id')
                    ->label('Kullanici')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Select::make('status')
                    ->label('Durum')
                    ->options(self::statusOptions())
                    ->required(),
            ]),
            Grid::make(2)->schema([
                Select::make('placement')
                    ->label('Reklam yeri')
                    ->options(self::placementOptions())
                    ->required(),
                TextInput::make('duration_days')
                    ->label('Sure gun')
                    ->numeric()
                    ->required(),
            ]),
            Grid::make(3)->schema([
                TextInput::make('width')
                    ->label('Genislik')
                    ->numeric()
                    ->required(),
                TextInput::make('height')
                    ->label('Yukseklik')
                    ->numeric(),
                TextInput::make('price_cents')
                    ->label('Fiyat kurus')
                    ->numeric()
                    ->required(),
            ]),
            Grid::make(2)->schema([
                TextInput::make('currency')
                    ->label('Para birimi')
                    ->maxLength(3)
                    ->required(),
                TextInput::make('title')
                    ->label('Baslik')
                    ->maxLength(80),
            ]),
            TextInput::make('target_url')
                ->label('Hedef baglanti')
                ->url()
                ->maxLength(2048)
                ->columnSpanFull(),
            FileUpload::make('image_path')
                ->label('Reklam gorseli')
                ->image()
                ->directory('ads/orders')
                ->disk('public')
                ->visibility('public')
                ->maxSize(4096)
                ->columnSpanFull(),
            Grid::make(3)->schema([
                DateTimePicker::make('paid_at')
                    ->label('Odeme tarihi')
                    ->seconds(false),
                DateTimePicker::make('starts_at')
                    ->label('Baslangic')
                    ->seconds(false),
                DateTimePicker::make('ends_at')
                    ->label('Bitis')
                    ->seconds(false),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Gorsel')
                    ->disk('public'),
                TextColumn::make('title')
                    ->label('Baslik')
                    ->placeholder('-')
                    ->searchable()
                    ->limit(28),
                TextColumn::make('user.name')
                    ->label('Kullanici')
                    ->placeholder('Misafir')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('placement')
                    ->label('Yer')
                    ->formatStateUsing(fn (string $state) => self::placementOptions()[$state] ?? $state)
                    ->sortable(),
                TextColumn::make('duration_days')
                    ->label('Sure')
                    ->suffix(' gun')
                    ->sortable(),
                TextColumn::make('price_cents')
                    ->label('Tutar')
                    ->formatStateUsing(fn (AdOrder $record) => $record->formatted_price)
                    ->sortable(),
                BadgeColumn::make('status')
                    ->label('Durum')
                    ->colors([
                        'warning' => 'pending_payment',
                        'info' => 'paid',
                        'success' => 'active',
                        'gray' => 'paused',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn (string $state) => self::statusOptions()[$state] ?? $state)
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Olusturulma')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('placement')
                    ->label('Reklam yeri')
                    ->options(self::placementOptions()),
                SelectFilter::make('status')
                    ->label('Durum')
                    ->options(self::statusOptions()),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdOrdersPage::route('/'),
            'create' => Pages\CreateAdOrderPage::route('/create'),
            'edit' => Pages\EditAdOrderPage::route('/{record}/edit'),
        ];
    }
}
