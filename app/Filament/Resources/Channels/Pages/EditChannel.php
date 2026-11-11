<?php

namespace App\Filament\Resources\Channels\Pages;

use App\Filament\Resources\Channels\ChannelResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

class EditChannel extends EditRecord
{
    protected static string $resource = ChannelResource::class;

    public function getMaxContentWidth(): Width
    {
        return Width::FiveExtraLarge;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
