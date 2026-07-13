<?php

namespace App\Filament\Resources\BrandingSettingResource\Pages;

use App\Filament\Resources\BrandingSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBrandingSetting extends EditRecord
{
    protected static string $resource = BrandingSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
        ];
    }
}

