<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Models\Channel;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Başlık')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (?string $state, Set $set) => $set('slug', Str::slug($state ?? ''))),
                TextInput::make('slug')
                    ->label('URL adresi')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Select::make('channel_id')
                    ->label('Kanal')
                    ->options(fn () => Channel::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
                Textarea::make('excerpt')
                    ->label('Kısa açıklama')
                    ->required()
                    ->maxLength(500)
                    ->rows(3)
                    ->columnSpanFull(),
                Textarea::make('body')
                    ->label('Yazı')
                    ->required()
                    ->rows(14)
                    ->columnSpanFull(),
                TextInput::make('cover_image')
                    ->label('Kapak görseli adresi')
                    ->maxLength(2048)
                    ->placeholder('https://... veya assets/img/...')
                    ->columnSpanFull(),
                TagsInput::make('tags')
                    ->label('Etiketler')
                    ->placeholder('Etiket yazıp Enter’a basın')
                    ->columnSpanFull(),
                DateTimePicker::make('published_at')
                    ->label('Yayın tarihi'),
                Toggle::make('is_published')
                    ->label('Yayında')
                    ->default(false),
                Toggle::make('is_adult')
                    ->label('18+ içerik')
                    ->helperText('Yetişkinlere yönelik içerikler, yaş filtresi kapalı kullanıcılara gizlenir.')
                    ->default(false),
            ]);
    }
}
