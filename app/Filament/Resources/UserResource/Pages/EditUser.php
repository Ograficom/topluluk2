<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Services\BadgeAwardSyncService;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        UserResource::ensureAdminIntegrity($this->record, $data);

        return UserResource::normalizeFormData($data);
    }

    protected function afterSave(): void
    {
        app(BadgeAwardSyncService::class)->syncForUser($this->record);
    }
}
