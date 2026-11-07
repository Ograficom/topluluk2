<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\BuildsMonthlyChart;
use App\Models\Post;
use Filament\Widgets\ChartWidget;

class PostsGrowthChart extends ChartWidget
{
    use BuildsMonthlyChart;

    protected ?string $heading = 'Yazı istatistikleri';
    protected ?string $description = 'Son 12 ayda oluşturulan yazılar.';
    protected static ?int $sort = 10;

    protected function getData(): array
    {
        return $this->monthlyChartData(Post::class, 'Yazılar', '#f97316');
    }
}
