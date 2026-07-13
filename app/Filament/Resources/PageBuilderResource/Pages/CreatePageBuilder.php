<?php

namespace App\Filament\Resources\PageBuilderResource\Pages;

use App\Filament\Resources\PageBuilderResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePageBuilder extends CreateRecord
{
    protected static string $resource = PageBuilderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['updated_by'] = auth()->id();

        return $data;
    }
}
