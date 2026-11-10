<?php

namespace App\Filament\Resources\Channels\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ChannelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Kanal adı')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (?string $state, Set $set) => $set('slug', Str::slug($state ?? ''))),
                TextInput::make('slug')
                    ->label('URL adresi')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Textarea::make('description')
                    ->label('Açıklama')
                    ->rows(4)
                    ->columnSpanFull(),
                \Filament\Schemas\Components\Section::make('SEO ayarları')
                    ->description('Bu kanalın Google sonuçlarında ve sosyal paylaşımlarda nasıl görüneceğini belirleyin.')
                    ->schema([
                        TextInput::make('meta_title')->label('Google sonuç başlığı')->maxLength(255)->helperText('Boşsa kanal adı ve sayfa içeriği kullanılır. Site adı otomatik eklenir.'),
                        Textarea::make('meta_description')->label('Google sonuç açıklaması')->rows(3)->maxLength(500)->helperText('Kanalın konusunu doğal bir dille özetleyin.')->columnSpanFull(),
                        TextInput::make('canonical_url')->label('Canonical URL')->url()->maxLength(2048)->helperText('Özel bir canonical adres kullanmıyorsanız boş bırakın.')->columnSpanFull(),
                        TextInput::make('og_image')->label('Sosyal paylaşım görseli URL')->url()->maxLength(2048)->helperText('Google sıralama alanı değildir; sosyal paylaşım kartında görünür. 1200 × 630 px önerilir.')->columnSpanFull(),
                        Toggle::make('robots_index')->label('Arama motorlarında indekslensin')->default(true),
                    ])->columns(2),
                Toggle::make('is_active')
                    ->label('Yayında')
                    ->default(true),
            ]);
    }
}
