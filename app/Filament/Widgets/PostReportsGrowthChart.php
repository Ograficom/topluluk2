<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\BuildsMonthlyChart;
use App\Models\PostReport;
use Filament\Widgets\ChartWidget;

class PostReportsGrowthChart extends ChartWidget
{
    use BuildsMonthlyChart;

    protected ?string $heading = 'Yazı şikayetleri istatistikleri';

    protected ?string $description = 'Son 12 ayda gönderilen yazı şikayetleri.';

    protected function getData(): array
    {
        return $this->monthlyChartData(PostReport::class, 'Yazı şikayetleri', '#ef4444');
    }
}
