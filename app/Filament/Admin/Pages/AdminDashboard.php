<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\AssetByCategoryChart;
use App\Filament\Admin\Widgets\AssetByStatusChart;
use App\Filament\Admin\Widgets\AssetStatsWidget;
use Filament\Pages\Dashboard;

class AdminDashboard extends Dashboard
{
    protected static string $routePath = '/';
    protected static ?string $title = 'Dashboard';

    public function getWidgets(): array
    {
        return [
            AssetStatsWidget::class,
            AssetByStatusChart::class,
            AssetByCategoryChart::class,
        ];
    }

    public function getColumns(): int | array
    {
        return 2;
    }
}
