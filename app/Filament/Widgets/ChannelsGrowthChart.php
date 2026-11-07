<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\BuildsMonthlyChart;
use App\Models\Channel;
use Filament\Widgets\ChartWidget;

class ChannelsGrowthChart extends ChartWidget
{
    use BuildsMonthlyChart;

    protected ?string $heading = 'Kanal istatistikleri';
    protected ?string $description = 'Son 12 ayda oluşturulan kanallar.';
    protected static ?int $sort = 11;

    protected function getData(): array
    {
        return $this->monthlyChartData(Channel::class, 'Kanallar', '#16a34a');
    }
}
