<?php

namespace App\Filament\Resources\PostReports\Pages;

use App\Filament\Resources\PostReports\PostReportResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPostReport extends EditRecord
{
    protected static string $resource = PostReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Şikayeti sil')
                ->requiresConfirmation(),
        ];
    }
}
