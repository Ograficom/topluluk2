<?php

namespace App\Filament\Resources\CookiePolicyResource\Pages;

use App\Filament\Resources\CookiePolicyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCookiePolicy extends EditRecord
{
    protected static string $resource = CookiePolicyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
