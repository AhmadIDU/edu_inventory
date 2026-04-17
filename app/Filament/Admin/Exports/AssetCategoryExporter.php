<?php

namespace App\Filament\Admin\Exports;

use App\Models\AssetCategory;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class AssetCategoryExporter extends Exporter
{
    protected static ?string $model = AssetCategory::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id'),
            ExportColumn::make('name'),
            ExportColumn::make('description'),
            ExportColumn::make('created_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        return 'Categories export complete: ' . number_format($export->successful_rows) . ' rows.';
    }
}
