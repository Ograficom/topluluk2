<?php

namespace App\ContentBlocks;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use VanOns\FilamentContentBuilder\Blocks\Contracts\Block;

class QuoteBlock extends Block
{
    public static function title(): string { return 'Alıntı'; }
    public static function icon(): ?string { return 'heroicon-o-chat-bubble-left-right'; }

    public static function schema(): array
    {
        return [
            Textarea::make('quote')->label('Alıntı metni')->rows(4)->maxLength(5000)->required(),
            TextInput::make('attribution')->label('Alıntı sahibi')->maxLength(180),
            TextInput::make('source_url')->label('Kaynak adresi')->url()->maxLength(2048),
        ];
    }
}
