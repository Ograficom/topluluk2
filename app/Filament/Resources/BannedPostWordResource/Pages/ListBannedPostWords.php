<?php

declare(strict_types=1);

namespace App\Filament\Resources\BannedPostWordResource\Pages;

use App\Filament\Resources\BannedPostWordResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBannedPostWords extends ListRecords
{
    protected static string $resource = BannedPostWordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Yasaklı kelime ekle'),
        ];
    }
}
