<?php

namespace App\Filament\Resources\MessageSettingResource\Pages;

use App\Filament\Resources\MessageSettingResource;
use App\Models\MessageSetting;
use Filament\Resources\Pages\ListRecords;

class ListMessageSettings extends ListRecords
{
    protected static string $resource = MessageSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Tek kayit, ekleme kapali.
        ];
    }

    public function mount(): void
    {
        parent::mount();
        MessageSetting::current();
    }
}
