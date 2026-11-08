<?php

namespace App\Filament\Resources\Advertisements\Pages;

use App\Filament\Resources\Advertisements\AdvertisementResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAdvertisement extends EditRecord
{
    protected static string $resource = AdvertisementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('Reklamı sil')->modalHeading('Reklam silinsin mi?'),
        ];
    }

    protected function getSaveFormAction(): \Filament\Actions\Action
    {
        return parent::getSaveFormAction()->label('Değişiklikleri kaydet');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Reklam bilgileri güncellendi';
    }
}
