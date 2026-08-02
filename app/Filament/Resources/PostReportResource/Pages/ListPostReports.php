<?php

namespace App\Filament\Resources\PostReportResource\Pages;

use App\Filament\Resources\PostReportResource;
use App\Filament\Resources\PostReportResource\Widgets\PendingReportsMetric;
use Filament\Resources\Pages\ListRecords;

class ListPostReports extends ListRecords
{
    protected static string $resource = PostReportResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PendingReportsMetric::class,
        ];
    }
}
