<?php

namespace App\ContentBlocks;

use App\Models\Post;
use Filament\Forms\Components\Select;
use VanOns\FilamentContentBuilder\Blocks\Contracts\Block;

class RelatedPostsBlock extends Block
{
    public static function title(): string { return 'İlgili gönderiler'; }
    public static function icon(): ?string { return 'heroicon-o-rectangle-stack'; }

    public static function schema(): array
    {
        return [
            Select::make('post_ids')->label('Gönderiler')->multiple()->searchable()
                ->options(fn (): array => Post::query()->where('is_published', true)
                    ->where(function ($query): void {
                        $query->whereNull('published_at')->orWhere('published_at', '<=', now());
                    })->orderByDesc('published_at')->limit(200)->pluck('title', 'id')->all())
                ->maxItems(6)->required(),
        ];
    }
}
