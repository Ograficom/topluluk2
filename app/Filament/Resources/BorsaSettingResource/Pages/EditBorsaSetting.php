<?php

namespace App\Filament\Resources\BorsaSettingResource\Pages;

use App\Filament\Resources\BorsaSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBorsaSetting extends EditRecord
{
    protected static string $resource = BorsaSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
        ];
    }
}
