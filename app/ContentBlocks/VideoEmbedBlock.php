<?php

namespace App\ContentBlocks;

use Filament\Forms\Components\TextInput;
use VanOns\FilamentContentBuilder\Blocks\Contracts\Block;

class VideoEmbedBlock extends Block
{
    public static function title(): string { return 'Video'; }
    public static function icon(): ?string { return 'heroicon-o-play-circle'; }

    public static function schema(): array
    {
        return [
            TextInput::make('url')->label('YouTube veya Vimeo adresi')->url()->maxLength(2048)->required()
                ->helperText('Yalnızca YouTube ve Vimeo bağlantıları ön yüzde gömülü olarak gösterilir.'),
            TextInput::make('caption')->label('Video açıklaması')->maxLength(300),
        ];
    }
}
