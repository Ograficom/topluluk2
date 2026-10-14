<?php

namespace App\ContentBlocks;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use VanOns\FilamentContentBuilder\Blocks\Contracts\Block;

class ImageGalleryBlock extends Block
{
    public static function title(): string { return 'Görsel galerisi'; }
    public static function icon(): ?string { return 'heroicon-o-photo'; }

    public static function schema(): array
    {
        return [
            FileUpload::make('images')->label('Görseller')->image()->multiple()
                ->reorderable()->disk('public')->directory('content-builder/galleries')
                ->visibility('public')->maxSize(10240)->maxFiles(20)->required(),
            TextInput::make('caption')->label('Galeri açıklaması')->maxLength(500),
        ];
    }
}
