<?php

namespace App\Filament\Resources\PageBuilderResource\Pages;

use App\Filament\Resources\PageBuilderResource;
use Filament\Resources\Pages\EditRecord;

class EditPageBuilder extends EditRecord
{
    protected static string $resource = PageBuilderResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();

        return $data;
    }
}
