<?php

namespace App\Filament\Resources\SocialLoginSettingResource\Pages;

use App\Filament\Resources\SocialLoginSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSocialLoginSetting extends EditRecord
{
    protected static string $resource = SocialLoginSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
        ];
    }
}
