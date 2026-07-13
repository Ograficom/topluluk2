<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Services\BadgeAwardSyncService;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return UserResource::normalizeFormData($data);
    }

    protected function afterCreate(): void
    {
        app(BadgeAwardSyncService::class)->syncForUser($this->record);
    }
}
