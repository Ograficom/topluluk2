<?php

namespace App\Filament\Resources\CommentBlockedWords\Pages;

use App\Filament\Resources\CommentBlockedWords\CommentBlockedWordResource;
use App\Services\CommentModerationService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCommentBlockedWord extends EditRecord
{
    protected static string $resource = CommentBlockedWordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        CommentModerationService::clearCache();
    }

    protected function afterDelete(): void
    {
        CommentModerationService::clearCache();
    }
}
