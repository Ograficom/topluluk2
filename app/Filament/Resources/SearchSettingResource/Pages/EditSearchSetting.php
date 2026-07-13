<?php

namespace App\Filament\Resources\SearchSettingResource\Pages;

use App\Filament\Resources\SearchSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSearchSetting extends EditRecord
{
    protected static string $resource = SearchSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
        ];
    }
}
