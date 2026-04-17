<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Asset;
use App\Models\AssetStatus;
use App\Models\AssetTransfer;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AssetStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $total = Asset::count();
        $activeStatus = AssetStatus::withoutGlobalScopes()
            ->where('is_system', true)
            ->where('name', 'like', '%Active%')
            ->first();

        $active = $activeStatus ? Asset::where('status_id', $activeStatus->id)->count() : 0;
        $thisMonthTransfers = AssetTransfer::whereMonth('transfer_date', now()->month)->count();

        $maintenanceStatus = AssetStatus::withoutGlobalScopes()
            ->where('is_system', true)
            ->where('name', 'like', '%Maintenance%')
            ->first();

        $underMaintenance = $maintenanceStatus ? Asset::where('status_id', $maintenanceStatus->id)->count() : 0;

        return [
            Stat::make('Total Assets', $total)
                ->description('All assets in inventory')
                ->color('primary'),

            Stat::make('Active / In Use', $active)
                ->description('Currently in use')
                ->color('success'),

            Stat::make('Under Maintenance', $underMaintenance)
                ->description('Being repaired or serviced')
                ->color('warning'),

            Stat::make('Transfers This Month', $thisMonthTransfers)
                ->description('Asset movements recorded')
                ->color('info'),
        ];
    }
}
