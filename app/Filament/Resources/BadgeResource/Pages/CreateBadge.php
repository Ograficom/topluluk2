<?php

namespace App\Filament\Resources\BadgeResource\Pages;

use App\Filament\Resources\BadgeResource;
use App\Services\BadgeAwardSyncService;
use Filament\Resources\Pages\CreateRecord;

class CreateBadge extends CreateRecord
{
    protected static string $resource = BadgeResource::class;

    protected function afterCreate(): void
    {
        app(BadgeAwardSyncService::class)->syncForBadge($this->record);
    }
}
