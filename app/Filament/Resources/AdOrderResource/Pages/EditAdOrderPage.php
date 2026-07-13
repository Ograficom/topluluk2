<?php

namespace App\Filament\Resources\AdOrderResource\Pages;

use App\Filament\Resources\AdOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAdOrderPage extends EditRecord
{
    protected static string $resource = AdOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
