<?php

namespace App\Filament\Resources\BorsaSettingResource\Pages;

use App\Filament\Resources\BorsaSettingResource;
use App\Models\BorsaSetting;
use Filament\Resources\Pages\ListRecords;

class ListBorsaSettings extends ListRecords
{
    protected static string $resource = BorsaSettingResource::class;

    public function mount(): void
    {
        BorsaSetting::current();

        parent::mount();
    }

    protected function getHeaderActions(): array
    {
        return [
            // Tek kayit, ekleme kapali.
        ];
    }
}
