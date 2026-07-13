<?php

namespace App\Filament\Resources\CookieConsentResource\Pages;

use App\Filament\Resources\CookieConsentResource;
use Filament\Resources\Pages\ListRecords;

class ListCookieConsents extends ListRecords
{
    protected static string $resource = CookieConsentResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
