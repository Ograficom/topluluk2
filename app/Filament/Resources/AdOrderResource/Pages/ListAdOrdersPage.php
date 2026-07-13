<?php

namespace App\Filament\Resources\AdOrderResource\Pages;

use App\Filament\Resources\AdOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAdOrdersPage extends ListRecords
{
    protected static string $resource = AdOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
