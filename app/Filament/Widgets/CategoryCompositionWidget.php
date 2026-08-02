<?php

namespace App\Filament\Widgets;

use App\Models\Post;
use LaBoiteACode\FilamentDashboardWidgets\Data\Composition;
use LaBoiteACode\FilamentDashboardWidgets\Data\CompositionSlice;
use LaBoiteACode\FilamentDashboardWidgets\Widgets\CompositionWidget;

class CategoryCompositionWidget extends CompositionWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 1;

    protected function getComposition(): Composition
    {
        $counts = Post::query()
            ->selectRaw('categories.name as category_name, count(*) as total')
            ->join('categories', 'categories.id', '=', 'posts.category_id')
            ->groupBy('categories.name')
            ->orderByDesc('total')
            ->pluck('total', 'category_name');

        $top = $counts->take(6);
        $otherTotal = (int) $counts->slice(6)->sum();

        $colors = ['primary', 'success', 'warning', 'info', 'danger', 'gray'];

        $slices = [];
        $index = 0;

        foreach ($top as $name => $total) {
            $slices[] = CompositionSlice::make((string) $name, (int) $total)
                ->color($colors[$index % count($colors)]);
            $index++;
        }

        if ($otherTotal > 0) {
            $slices[] = CompositionSlice::make('Diğer', $otherTotal)->color('gray');
        }

        return Composition::make('Kategoriye Göre Gönderiler')
            ->type('doughnut')
            ->slices($slices);
    }
}
