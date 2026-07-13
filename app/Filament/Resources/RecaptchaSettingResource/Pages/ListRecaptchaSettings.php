<?php

namespace App\Filament\Resources\RecaptchaSettingResource\Pages;

use App\Filament\Resources\RecaptchaSettingResource;
use App\Models\RecaptchaSetting;
use Filament\Resources\Pages\ListRecords;

class ListRecaptchaSettings extends ListRecords
{
    protected static string $resource = RecaptchaSettingResource::class;

    public function mount(): void
    {
        RecaptchaSetting::current();

        parent::mount();
    }

    protected function getHeaderActions(): array
    {
        return [
            // Tek kayit, ekleme kapali.
        ];
    }
}

