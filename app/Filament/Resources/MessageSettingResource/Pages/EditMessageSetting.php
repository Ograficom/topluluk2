<?php

namespace App\Filament\Resources\MessageSettingResource\Pages;

use App\Filament\Resources\MessageSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMessageSetting extends EditRecord
{
    protected static string $resource = MessageSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
        ];
    }
}
