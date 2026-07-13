<?php

namespace App\Filament\Resources\ThemeSettingResource\Pages;

use App\Filament\Resources\ThemeSettingResource;
use App\Models\ThemeSetting;
use Filament\Resources\Pages\ListRecords;

class ListThemeSettings extends ListRecords
{
    protected static string $resource = ThemeSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Tek kayit, ekleme kapali.
        ];
    }

    public function mount(): void
    {
        parent::mount();
        ThemeSetting::current();
    }
}
