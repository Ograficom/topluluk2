<?php

namespace App\Filament\Resources\CommentResource\Pages;

use App\Filament\Resources\CommentResource;
use App\Filament\Resources\CommentResource\Widgets\PendingCommentsMetric;
use Asmit\ResizedColumn\HasResizableColumn;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListComments extends ListRecords
{
    use HasResizableColumn;

    protected static string $resource = CommentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PendingCommentsMetric::class,
        ];
    }
}
