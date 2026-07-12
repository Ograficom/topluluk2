<?php

namespace App\Filament\Resources\AdsResource\Pages;

use App\Filament\Resources\AdsResource;
use Filament\Resources\Pages\EditRecord;

class EditAds extends EditRecord
{
    protected static string $resource = AdsResource::class;

    public static function getPluralModelLabel(): string
    {
        return __('Advertisements');
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
