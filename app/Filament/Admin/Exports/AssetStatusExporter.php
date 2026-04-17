<?php

namespace App\Filament\Admin\Exports;

use App\Models\AssetStatus;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class AssetStatusExporter extends Exporter
{
    protected static ?string $model = AssetStatus::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id'),
            ExportColumn::make('name'),
            ExportColumn::make('color'),
            ExportColumn::make('is_system'),
            ExportColumn::make('created_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        return 'Statuses export complete: ' . number_format($export->successful_rows) . ' rows.';
    }
}
