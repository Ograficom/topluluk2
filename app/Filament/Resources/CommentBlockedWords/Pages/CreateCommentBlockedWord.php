<?php

namespace App\Filament\Resources\CommentBlockedWords\Pages;

use App\Filament\Resources\CommentBlockedWords\CommentBlockedWordResource;
use App\Services\CommentModerationService;
use Filament\Resources\Pages\CreateRecord;

class CreateCommentBlockedWord extends CreateRecord
{
    protected static string $resource = CommentBlockedWordResource::class;

    protected function afterCreate(): void
    {
        CommentModerationService::clearCache();
    }
}
