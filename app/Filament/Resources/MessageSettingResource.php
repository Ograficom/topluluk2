<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MessageSettingResource\Pages;
use App\Models\MessageSetting;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MessageSettingResource extends Resource
{
    protected static ?string $model = MessageSetting::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Ayarlar';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static bool $isGloballySearchable = false;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Placeholder::make('general_heading')->label('Mesaj Ayarlari'),
            Grid::make(2)->schema([
                Toggle::make('is_enabled')->label('Mesajlari aktif et'),
                Toggle::make('allow_following_only')->label('Sadece takip edilenlere izin ver'),
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
                IconColumn::make('allow_following_only')
                    ->label('Takip edene izin')
                    ->boolean(),
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
            'index' => Pages\ListMessageSettings::route('/'),
            'edit' => Pages\EditMessageSetting::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->limit(1);
    }
}











