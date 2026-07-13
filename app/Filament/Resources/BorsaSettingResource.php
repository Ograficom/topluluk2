<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BorsaSettingResource\Pages;
use App\Models\BorsaSetting;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BorsaSettingResource extends Resource
{
    protected static ?string $model = BorsaSetting::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Ayarlar';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Borsa';

    protected static ?string $modelLabel = 'Borsa';

    protected static ?string $pluralModelLabel = 'Borsa';

    protected static bool $isGloballySearchable = false;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Toggle::make('is_active')
                ->label('Aktif')
                ->inline(false),
            TextInput::make('api_url')
                ->label('API adresi')
                ->placeholder('https://api.example.com/quotes?symbols={symbols}&apikey={apikey}')
                ->helperText('URL icinde {symbols}, {symbols_csv}, {symbols_json} ve {apikey} kullanabilirsiniz.')
                ->columnSpanFull(),
            TextInput::make('api_key')
                ->label('API anahtari')
                ->password()
                ->revealable()
                ->columnSpanFull(),
            Textarea::make('symbols')
                ->label('Semboller')
                ->rows(2)
                ->helperText('Virgulle ayirin. Ornek: XU100,USDTRY,EURTRY,BTCUSDT')
                ->columnSpanFull(),
            Grid::make(2)->schema([
                TextInput::make('base_symbol')
                    ->label('Temel sembol')
                    ->default('USD')
                    ->helperText('API base para birimi. Ornek: USD'),
                TextInput::make('pair_symbols')
                    ->label('Gosterilecek pariteler')
                    ->helperText('Virgulle ayirin. Ornek: USD/TRY,EUR/TRY'),
            ]),
            Grid::make(2)->schema([
                TextInput::make('response_path')
                    ->label('Yanit yolu')
                    ->placeholder('data')
                    ->helperText('JSON icindeki array yolunu yazin. Bos birakirsaniz root kullanilir.'),
                TextInput::make('cache_seconds')
                    ->label('Cache (saniye)')
                    ->numeric()
                    ->default(60),
            ]),
            Grid::make(3)->schema([
                TextInput::make('symbol_key')
                    ->label('Sembol anahtari')
                    ->default('symbol'),
                TextInput::make('price_key')
                    ->label('Fiyat anahtari')
                    ->default('price'),
                TextInput::make('change_key')
                    ->label('Degisim anahtari')
                    ->default('change'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('api_url')
                    ->label('API adresi')
                    ->limit(50),
                TextColumn::make('pair_symbols')
                    ->label('Pariteler')
                    ->limit(30),
                TextColumn::make('symbols')
                    ->label('Semboller')
                    ->limit(40),
                TextColumn::make('updated_at')
                    ->label('Guncellendi')
                    ->dateTime()
                    ->since(),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([])
            ->paginated(false);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBorsaSettings::route('/'),
            'edit' => Pages\EditBorsaSetting::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->limit(1);
    }
}
