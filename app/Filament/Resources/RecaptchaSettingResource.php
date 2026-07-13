<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RecaptchaSettingResource\Pages;
use App\Models\RecaptchaSetting;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
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

class RecaptchaSettingResource extends Resource
{
    protected static ?string $model = RecaptchaSetting::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Ayarlar';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'reCAPTCHA';

    protected static ?string $modelLabel = 'reCAPTCHA';

    protected static ?string $pluralModelLabel = 'reCAPTCHA';

    protected static bool $isGloballySearchable = false;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Placeholder::make('general_heading')->label('Genel'),
            Grid::make(2)->schema([
                Toggle::make('is_enabled')->label('reCAPTCHA aktif'),
                Toggle::make('verify_action')->label('Action dogrula (v3)'),
            ]),
            Grid::make(2)->schema([
                Toggle::make('login_enabled')->label('Giris formu'),
                Toggle::make('register_enabled')->label('Kayit formu'),
                Toggle::make('comment_enabled')->label('Yorum formu'),
            ]),

            Placeholder::make('keys_heading')->label('Anahtarlar (v3)'),
            Grid::make(2)->schema([
                TextInput::make('site_key')
                    ->label('Site Key')
                    ->autocomplete('off')
                    ->helperText('Bos birakirsaniz .env (RECAPTCHA_SITE_KEY) kullanilir.'),
                TextInput::make('secret_key')
                    ->label('Secret Key')
                    ->password()
                    ->revealable()
                    ->autocomplete('off')
                    ->helperText('Bos birakirsaniz .env (RECAPTCHA_SECRET_KEY) kullanilir.'),
            ]),

            Placeholder::make('rules_heading')->label('Kurallar'),
            Grid::make(2)->schema([
                TextInput::make('minimum_score')
                    ->label('Minimum skor (0-1)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(1)
                    ->step(0.1)
                    ->required(),
                Textarea::make('allowed_hostnames')
                    ->label('Izinli hostnameler (opsiyonel)')
                    ->rows(2)
                    ->placeholder('ornek.com, www.ornek.com')
                    ->helperText('Virgul ile ayirin; bos birakilirsa kontrol edilmez.'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('is_enabled')->label('Aktif')->boolean(),
                IconColumn::make('login_enabled')->label('Giris')->boolean(),
                IconColumn::make('register_enabled')->label('Kayit')->boolean(),
                IconColumn::make('comment_enabled')->label('Yorum')->boolean(),
                TextColumn::make('minimum_score')->label('Min skor'),
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
            'index' => Pages\ListRecaptchaSettings::route('/'),
            'edit' => Pages\EditRecaptchaSetting::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->limit(1);
    }
}












