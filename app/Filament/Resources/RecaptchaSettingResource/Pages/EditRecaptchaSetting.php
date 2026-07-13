<?php

namespace App\Filament\Resources\RecaptchaSettingResource\Pages;

use App\Filament\Resources\RecaptchaSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRecaptchaSetting extends EditRecord
{
    protected static string $resource = RecaptchaSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
        ];
    }
}

