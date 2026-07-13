<?php

namespace App\Filament\Resources\BrandingSettingResource\Pages;

use App\Filament\Resources\BrandingSettingResource;
use App\Models\BrandingSetting;
use Filament\Resources\Pages\ListRecords;

class ListBrandingSettings extends ListRecords
{
    protected static string $resource = BrandingSettingResource::class;

    public function mount(): void
    {
        BrandingSetting::current();

        parent::mount();
    }

    protected function getHeaderActions(): array
    {
        return [
            // Tek kayit, ekleme kapali.
        ];
    }
}

