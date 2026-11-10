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
                        TextInput::make('meta_title')->label('SEO başlığı')->maxLength(255),
                        Textarea::make('meta_description')->label('Meta açıklaması')->rows(3)->maxLength(500)->columnSpanFull(),
                        TextInput::make('meta_keywords')->label('Anahtar kelimeler')->maxLength(2000)->columnSpanFull(),
                        TextInput::make('canonical_url')->label('Canonical URL')->url()->maxLength(2048)->columnSpanFull(),
                        TextInput::make('og_image')->label('Sosyal paylaşım görseli URL')->url()->maxLength(2048)->columnSpanFull(),
                        Toggle::make('robots_index')->label('Arama motorlarında indekslensin')->default(true),
                    ])->columns(2),
                Toggle::make('is_active')
                    ->label('Yayında')
                    ->default(true),
            ]);
    }
}
