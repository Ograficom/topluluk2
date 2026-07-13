<?php

namespace App\Filament\Resources\RssSourceResource\Pages;

use App\Filament\Resources\RssSourceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRssSources extends ListRecords
{
    protected static string $resource = RssSourceResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->label('Yeni RSS kaynağı')];
    }
}
