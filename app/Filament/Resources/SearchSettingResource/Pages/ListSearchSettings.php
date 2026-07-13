<?php

namespace App\Filament\Resources\SearchSettingResource\Pages;

use App\Filament\Resources\SearchSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSearchSettings extends ListRecords
{
    protected static string $resource = SearchSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Tek kayıt, ekleme kapalı.
        ];
    }
}
