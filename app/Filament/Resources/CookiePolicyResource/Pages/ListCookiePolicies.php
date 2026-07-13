<?php

namespace App\Filament\Resources\CookiePolicyResource\Pages;

use App\Filament\Resources\CookiePolicyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCookiePolicies extends ListRecords
{
    protected static string $resource = CookiePolicyResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [];

        if (CookiePolicyResource::canCreate()) {
            $actions[] = Actions\CreateAction::make();
        }

        return $actions;
    }
}
