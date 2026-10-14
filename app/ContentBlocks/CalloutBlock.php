<?php

namespace App\ContentBlocks;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use VanOns\FilamentContentBuilder\Blocks\Contracts\Block;

class CalloutBlock extends Block
{
    public static function title(): string { return 'Bilgi kutusu'; }
    public static function icon(): ?string { return 'heroicon-o-information-circle'; }

    public static function schema(): array
    {
        return [
            TextInput::make('heading')->label('Başlık')->maxLength(160),
            Select::make('tone')->label('Tür')->options([
                'info' => 'Bilgi', 'success' => 'Başarılı', 'warning' => 'Uyarı', 'neutral' => 'Standart',
            ])->default('info')->required(),
            Textarea::make('body')->label('Açıklama')->rows(4)->maxLength(3000)->required(),
        ];
    }
}
