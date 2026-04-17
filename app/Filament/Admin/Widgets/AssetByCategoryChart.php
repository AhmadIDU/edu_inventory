<?php

namespace App\Filament\Admin\Widgets;

use App\Models\AssetCategory;
use Filament\Widgets\ChartWidget;

class AssetByCategoryChart extends ChartWidget
{
    protected ?string $heading = 'Assets by Category';

    protected function getData(): array
    {
        $categories = AssetCategory::withCount('assets')->get();

        $colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4', '#84cc16'];

        return [
            'datasets' => [
                [
                    'label'           => 'Assets',
                    'data'            => $categories->pluck('assets_count')->toArray(),
                    'backgroundColor' => collect($colors)->take($categories->count())->values()->toArray(),
                ],
            ],
            'labels' => $categories->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
