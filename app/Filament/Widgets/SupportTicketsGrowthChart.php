<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\BuildsMonthlyChart;
use App\Models\SupportTicket;
use Filament\Widgets\ChartWidget;

class SupportTicketsGrowthChart extends ChartWidget
{
    use BuildsMonthlyChart;

    protected ?string $heading = 'İletişim formu istatistikleri';
    protected ?string $description = 'Son 12 ayda gelen destek ve geri bildirim talepleri.';
    protected static ?int $sort = 13;

    protected function getData(): array
    {
        return $this->monthlyChartData(SupportTicket::class, 'İletişim formları', '#a855f7');
    }
}
