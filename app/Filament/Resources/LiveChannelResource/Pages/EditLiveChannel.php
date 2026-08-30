<?php

namespace App\Filament\Resources\LiveChannelResource\Pages;

use App\Filament\Resources\LiveChannelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLiveChannel extends EditRecord
{
    protected static string $resource = LiveChannelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
