<?php

namespace App\Filament\Resources\CommentBlockedWords\Pages;

use App\Filament\Resources\CommentBlockedWords\CommentBlockedWordResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCommentBlockedWords extends ListRecords
{
    protected static string $resource = CommentBlockedWordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
