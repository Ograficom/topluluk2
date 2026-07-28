<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SocialLinkResource\Pages;
use App\Models\SocialLink;
use Filament\Actions;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SocialLinkResource extends Resource
{
    protected static ?string $model = SocialLink::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Sayfalar ve SEO';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-share';

    protected static ?string $navigationLabel = 'Sosyal Medya';

    protected static ?string $modelLabel = 'Sosyal Medya Hesabı';

    protected static ?string $pluralModelLabel = 'Sosyal Medya Hesapları';

    /**
     * Kullanici adi girildiginde otomatik olarak olusturulacak link ornegi.
     */
    private static function placeholderFor(string $platform): string
    {
        return match ($platform) {
            'whatsapp' => 'Örn. 905XXXXXXXXX (ülke kodlu, boşluksuz) veya tam wa.me linki',
            'discord' => 'Örn. sunucu davet kodu (abc123) veya tam discord.gg linki',
            'youtube' => 'Örn. kanal-adi veya tam youtube.com linki',
            'tiktok' => 'Örn. kullanici.adi veya tam tiktok.com linki',
            default => 'Örn. kullanici.adi veya tam bağlantı (https://...)',
        };
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Grid::make(3)->schema([
                Placeholder::make('platform_display')
                    ->label('Platform')
                    ->content(fn (?SocialLink $record) => $record?->label ?? '-')
                    ->columnSpan(1),
                TextInput::make('value')
                    ->label('Kullanıcı Adı veya Bağlantı')
                    ->maxLength(255)
                    ->placeholder(fn (?SocialLink $record) => self::placeholderFor($record?->platform ?? ''))
                    ->helperText('Sadece kullanıcı adı yazabilirsin, sitede otomatik doğru bağlantıya çevrilir. Tam bağlantı da (https://...) girilebilir.')
                    ->columnSpan(2),
            ]),
            Toggle::make('is_active')
                ->label('Sitede Göster')
                ->helperText('Kapalıyken bu ikon sol menüde görünmez.')
                ->default(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('label')
                    ->label('Platform')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('value')
                    ->label('Kullanıcı Adı / Bağlantı')
                    ->placeholder('Girilmedi')
                    ->limit(40),
                TextColumn::make('url')
                    ->label('Yayınlanan Bağlantı')
                    ->placeholder('-')
                    ->limit(40)
                    ->url(fn (SocialLink $record) => $record->url, shouldOpenInNewTab: true)
                    ->color('primary'),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([])
            ->actions([
                Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->orderBy('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSocialLinks::route('/'),
            'edit' => Pages\EditSocialLink::route('/{record}/edit'),
        ];
    }
}
