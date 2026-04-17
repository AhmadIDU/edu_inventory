<?php

namespace App\Filament\SuperAdmin\Widgets;

use App\Models\Institution;
use Filament\Widgets\ChartWidget;

class InstitutionBreakdownWidget extends ChartWidget
{
    protected ?string $heading = 'Assets per Institution';

    protected function getData(): array
    {
        $institutions = Institution::withCount('assets')->get();

        $colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'];

        return [
            'datasets' => [
                [
                    'label'           => 'Assets',
                    'data'            => $institutions->pluck('assets_count')->toArray(),
                    'backgroundColor' => collect($colors)->take($institutions->count())->values()->toArray(),
                ],
            ],
            'labels' => $institutions->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
