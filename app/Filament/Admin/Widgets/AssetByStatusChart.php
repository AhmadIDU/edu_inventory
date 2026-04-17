<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Asset;
use App\Models\AssetStatus;
use Filament\Widgets\ChartWidget;

class AssetByStatusChart extends ChartWidget
{
    protected ?string $heading = 'Assets by Status';

    protected function getData(): array
    {
        $statuses = AssetStatus::withCount('assets')->get();

        return [
            'datasets' => [
                [
                    'label' => 'Assets',
                    'data'  => $statuses->pluck('assets_count')->toArray(),
                    'backgroundColor' => $statuses->pluck('color')->toArray(),
                ],
            ],
            'labels' => $statuses->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
