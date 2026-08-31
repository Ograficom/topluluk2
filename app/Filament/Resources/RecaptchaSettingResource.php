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
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RecaptchaSettingResource extends Resource
{
    protected static ?string $model = RecaptchaSetting::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Ayarlar';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Giriş Güvenliği';

    protected static ?string $modelLabel = 'Giriş Güvenliği';

    protected static ?string $pluralModelLabel = 'Giriş Güvenliği';

    protected static bool $isGloballySearchable = false;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Placeholder::make('login_security_heading')->label('Giriş güvenliği'),
            Grid::make(2)->schema([
                Toggle::make('block_vpn_logins')
                    ->label('VPN / proxy girişlerini engelle')
                    ->helperText('Bilinen VPN ve proxy ağlarından parola ile girişi engeller.'),
                Toggle::make('block_tor_logins')
                    ->label('Tor girişlerini engelle')
                    ->helperText('Güncel Tor çıkış düğümü listesini kontrol eder.'),
                Toggle::make('verify_unknown_devices')
                    ->label('Bilinmeyen cihaz doğrulaması')
                    ->helperText('Yeni cihazlarda e-posta ile 6 haneli kod ister.'),
                Toggle::make('bot_honeypot_enabled')
                    ->label('Bot / otomasyon kontrolü')
                    ->helperText('Honeypot ve belirgin otomasyon istemcilerini girişte engeller.'),
            ]),
            TextInput::make('trusted_device_days')
                ->label('Cihazı güvenilir tutma süresi (gün)')
                ->numeric()
                ->minValue(1)
                ->maxValue(365)
                ->default(90)
                ->required(),

            Placeholder::make('general_heading')->label('reCAPTCHA v3'),
            Grid::make(2)->schema([
                Toggle::make('is_enabled')->label('reCAPTCHA aktif'),
                Toggle::make('verify_action')->label('Action doğrula (v3)'),
            ]),
            Grid::make(2)->schema([
                Toggle::make('login_enabled')->label('Giriş formu'),
                Toggle::make('register_enabled')->label('Kayıt formu'),
                Toggle::make('comment_enabled')->label('Yorum formu'),
            ]),

            Placeholder::make('keys_heading')->label('Anahtarlar (v3)'),
            Grid::make(2)->schema([
                TextInput::make('site_key')
                    ->label('Site Key')
                    ->autocomplete('off')
                    ->helperText('Boş bırakırsanız .env (RECAPTCHA_SITE_KEY) kullanılır.'),
                TextInput::make('secret_key')
                    ->label('Secret Key')
                    ->password()
                    ->revealable()
                    ->autocomplete('off')
                    ->helperText('Boş bırakırsanız .env (RECAPTCHA_SECRET_KEY) kullanılır.'),
            ]),

            Placeholder::make('rules_heading')->label('reCAPTCHA kuralları'),
            Grid::make(2)->schema([
                TextInput::make('minimum_score')
                    ->label('Minimum skor (0-1)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(1)
                    ->step(0.1)
                    ->required(),
                Textarea::make('allowed_hostnames')
                    ->label('İzinli hostnameler (opsiyonel)')
                    ->rows(2)
                    ->placeholder('ografi.com, www.ografi.com')
                    ->helperText('Virgül ile ayırın; boş bırakılırsa kontrol edilmez.'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('block_vpn_logins')->label('VPN')->boolean(),
                IconColumn::make('block_tor_logins')->label('Tor')->boolean(),
                IconColumn::make('verify_unknown_devices')->label('Yeni cihaz')->boolean(),
                IconColumn::make('bot_honeypot_enabled')->label('Bot')->boolean(),
                IconColumn::make('is_enabled')->label('reCAPTCHA')->boolean(),
                TextColumn::make('trusted_device_days')->label('Güvenilir gün'),
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
