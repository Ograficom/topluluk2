<?php

namespace App\Filament\Resources\SocialLoginSettingResource\Pages;

use App\Filament\Resources\SocialLoginSettingResource;
use App\Models\SocialLoginSetting;
use Filament\Resources\Pages\ListRecords;

class ListSocialLoginSettings extends ListRecords
{
    protected static string $resource = SocialLoginSettingResource::class;

    public function mount(): void
    {
        SocialLoginSetting::current();

        parent::mount();
    }

    protected function getHeaderActions(): array
    {
        return [
            // Tek kayit, ekleme kapali.
        ];
    }
}
