<?php

namespace App\ContentBlocks;

use Filament\Forms\Components\TextInput;
use VanOns\FilamentContentBuilder\Blocks\Contracts\Block;

class SourceBoxBlock extends Block
{
    public static function title(): string { return 'Kaynakça kutusu'; }
    public static function icon(): ?string { return 'heroicon-o-link'; }

    public static function schema(): array
    {
        return [
            TextInput::make('label')->label('Kaynak adı')->maxLength(180)->required(),
            TextInput::make('url')->label('Kaynak adresi')->url()->maxLength(2048)->required(),
            TextInput::make('author')->label('Yazar / yayıncı')->maxLength(180),
            TextInput::make('published_at')->label('Yayın tarihi')->maxLength(80),
            TextInput::make('accessed_at')->label('Erişim tarihi')->maxLength(80),
        ];
    }
}
