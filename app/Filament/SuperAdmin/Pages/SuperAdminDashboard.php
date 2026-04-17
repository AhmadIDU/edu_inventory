<?php

namespace App\Filament\SuperAdmin\Pages;

use App\Filament\SuperAdmin\Widgets\GlobalStatsWidget;
use App\Filament\SuperAdmin\Widgets\InstitutionBreakdownWidget;
use Filament\Pages\Dashboard;

class SuperAdminDashboard extends Dashboard
{
    protected static string $routePath = '/';
    protected static ?string $title = 'Dashboard';

    public function getWidgets(): array
    {
        return [
            GlobalStatsWidget::class,
            InstitutionBreakdownWidget::class,
        ];
    }

    public function getColumns(): int | array
    {
        return 2;
    }
}
