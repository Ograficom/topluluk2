<?php

namespace App\Filament\Resources\CommentBlockedWords\Pages;

use App\Filament\Resources\CommentBlockedWords\CommentBlockedWordResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCommentBlockedWord extends ViewRecord
{
    protected static string $resource = CommentBlockedWordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
