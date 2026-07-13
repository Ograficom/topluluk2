<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SocialLoginSettingResource\Pages;
use App\Models\SocialLoginSetting;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SocialLoginSettingResource extends Resource
{
    protected static ?string $model = SocialLoginSetting::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Ayarlar';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-key';

    protected static ?string $navigationLabel = 'Sosyal Giris';

    protected static ?string $modelLabel = 'Sosyal Giris';

    protected static ?string $pluralModelLabel = 'Sosyal Giris';

    protected static bool $isGloballySearchable = false;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Placeholder::make('general_heading')->label('Genel'),
            Toggle::make('is_enabled')->label('Sosyal giris aktif'),

            Placeholder::make('google_heading')->label('Google'),
            Grid::make(2)->schema([
                Toggle::make('google_enabled')->label('Google aktif'),
                TextInput::make('google_client_id')
                    ->label('Google istemci kimligi')
                    ->autocomplete('off'),
                TextInput::make('google_client_secret')
                    ->label('Google istemci gizli anahtari')
                    ->password()
                    ->revealable()
                    ->autocomplete('off'),
                TextInput::make('google_redirect_url')
                    ->label('Google yonlendirme URL')
                    ->placeholder(route('social.callback', 'google'))
                    ->autocomplete('off'),
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
                IconColumn::make('google_enabled')
                    ->label('Google')
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
            'index' => Pages\ListSocialLoginSettings::route('/'),
            'edit' => Pages\EditSocialLoginSetting::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->limit(1);
    }
}











