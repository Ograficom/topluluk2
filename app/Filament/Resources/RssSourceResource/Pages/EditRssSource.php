<?php

namespace App\Filament\Resources\RssSourceResource\Pages;

use App\Filament\Resources\RssSourceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRssSource extends EditRecord
{
    protected static string $resource = RssSourceResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
