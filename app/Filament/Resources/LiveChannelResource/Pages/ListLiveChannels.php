<?php

namespace App\Filament\Resources\LiveChannelResource\Pages;

use App\Filament\Resources\LiveChannelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLiveChannels extends ListRecords
{
    protected static string $resource = LiveChannelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Kanal ekle'),
        ];
    }
}
