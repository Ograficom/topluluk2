<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SearchSettingResource\Pages;
use App\Models\SearchSetting;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SearchSettingResource extends Resource
{
    protected static ?string $model = SearchSetting::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Ayarlar';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-magnifying-glass-circle';

    protected static bool $isGloballySearchable = false;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Placeholder::make('general_heading')->label('Genel'),
            Grid::make(2)->schema([
                Toggle::make('is_enabled')->label('Aramayı aktif et'),
                Toggle::make('include_post_content')->label('Yazı içeriğinde ara'),
            ]),

            Placeholder::make('scope_heading')->label('Kapsam'),
            Grid::make(2)->schema([
                Toggle::make('include_posts')->label('Yazılar'),
                Toggle::make('include_categories')->label('Kategoriler'),
                Toggle::make('include_tags')->label('Etiketler'),
                Toggle::make('include_users')->label('Kullanıcılar'),
            ]),

            Placeholder::make('limits_heading')->label('Limitler'),
            Grid::make(2)->schema([
                TextInput::make('limit_per_type')
                    ->label('Her tip için sonuç limiti')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(20)
                    ->default(5)
                    ->required(),
                TextInput::make('min_query_length')
                    ->label('Minimum sorgu uzunluğu')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(10)
                    ->default(2)
                    ->required(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('is_enabled')
                    ->label('Aktif')
                    ->boolean(),
                IconColumn::make('include_posts')
                    ->label('Yazılar')
                    ->boolean(),
                IconColumn::make('include_categories')
                    ->label('Kategoriler')
                    ->boolean(),
                IconColumn::make('include_tags')
                    ->label('Etiketler')
                    ->boolean(),
                IconColumn::make('include_users')
                    ->label('Kullanıcılar')
                    ->boolean(),
                TextColumn::make('limit_per_type')
                    ->label('Limit'),
                TextColumn::make('min_query_length')
                    ->label('Min uzunluk'),
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
            'index' => Pages\ListSearchSettings::route('/'),
            'edit' => Pages\EditSearchSetting::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->limit(1);
    }
}











