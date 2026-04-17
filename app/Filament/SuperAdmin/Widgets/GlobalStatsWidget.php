<?php

namespace App\Filament\SuperAdmin\Widgets;

use App\Models\Asset;
use App\Models\AssetTransfer;
use App\Models\Institution;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class GlobalStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Institutions', Institution::count())
                ->description('Registered institutions')
                ->color('primary'),

            Stat::make('Active Institutions', Institution::where('is_active', true)->count())
                ->description('Currently active')
                ->color('success'),

            Stat::make('Total Assets', Asset::withoutGlobalScopes()->count())
                ->description('Across all institutions')
                ->color('info'),

            Stat::make('Institution Admins', User::whereHas('roles', fn ($q) => $q->where('name', 'institution_admin'))->count())
                ->description('Active admin accounts')
                ->color('warning'),
        ];
    }
}
