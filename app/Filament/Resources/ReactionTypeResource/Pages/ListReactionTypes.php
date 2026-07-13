<?php

namespace App\Filament\Resources\ReactionTypeResource\Pages;

use App\Filament\Resources\ReactionTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReactionTypes extends ListRecords
{
    protected static string $resource = ReactionTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
