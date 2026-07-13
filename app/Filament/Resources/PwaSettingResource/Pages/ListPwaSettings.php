<?php

namespace App\Filament\Resources\PwaSettingResource\Pages;

use App\Filament\Resources\PwaSettingResource;
use App\Models\PwaSetting;
use Filament\Resources\Pages\ListRecords;

class ListPwaSettings extends ListRecords
{
    protected static string $resource = PwaSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function mount(): void
    {
        parent::mount();
        PwaSetting::current();
    }
}
