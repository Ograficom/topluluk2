<?php

namespace App\Filament\Resources\PageBuilderResource\Pages;

use App\Filament\Resources\PageBuilderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPageBuilders extends ListRecords
{
    protected static string $resource = PageBuilderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
