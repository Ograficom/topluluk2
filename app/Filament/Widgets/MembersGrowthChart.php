<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\BuildsMonthlyChart;
use App\Models\User;
use Filament\Widgets\ChartWidget;

class MembersGrowthChart extends ChartWidget
{
    use BuildsMonthlyChart;

    protected ?string $heading = 'Üye istatistikleri';
    protected ?string $description = 'Son 12 ayda kayıt olan üyeler.';
    protected static ?int $sort = 12;

    protected function getData(): array
    {
        return $this->monthlyChartData(User::class, 'Üyeler', '#2563eb');
    }
}
