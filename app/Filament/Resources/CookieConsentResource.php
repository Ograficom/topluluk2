<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CookieConsentResource\Pages;
use App\Models\CookieConsent;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CookieConsentResource extends Resource
{
    protected static ?string $model = CookieConsent::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-finger-print';

    protected static string | \UnitEnum | null $navigationGroup = 'Ayarlar';

    protected static ?string $navigationLabel = 'Çerez Onayları';

    protected static ?string $modelLabel = 'Çerez Onayı';

    protected static ?string $pluralModelLabel = 'Çerez Onayları';

    protected static bool $isGloballySearchable = false;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Kullanıcı')
                    ->toggleable()
                    ->placeholder('Misafir'),
                TextColumn::make('device_id')
                    ->label('Cihaz ID')
                    ->extraAttributes(['class' => 'font-mono'])
                    ->copyable()
                    ->searchable(),
                TextColumn::make('ip_address')
                    ->label('IP')
                    ->searchable(),
                TextColumn::make('policy_version')
                    ->label('Sürüm')
                    ->badge(),
                IconColumn::make('accepted')
                    ->label('Durum')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
                TextColumn::make('user_agent')
                    ->label('Cihaz / Tarayıcı')
                    ->limit(40)
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Tarih')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('accepted')->label('Kabul'),
            ])
            ->actions([])
            ->bulkActions([])
            ->emptyStateHeading('Henüz kayıt yok');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCookieConsents::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user', 'policy']);
    }
}







