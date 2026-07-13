<?php

namespace App\Filament\Resources\ReactionTypeResource\Pages;

use App\Filament\Resources\ReactionTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReactionType extends EditRecord
{
    protected static string $resource = ReactionTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
