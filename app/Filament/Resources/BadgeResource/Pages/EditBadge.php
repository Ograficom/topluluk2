<?php

namespace App\Filament\Resources\BadgeResource\Pages;

use App\Filament\Resources\BadgeResource;
use App\Services\BadgeAwardSyncService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBadge extends EditRecord
{
    protected static string $resource = BadgeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        app(BadgeAwardSyncService::class)->syncForBadge($this->record);
    }
}
