<?php

namespace App\Filament\Admin\Exports;

use App\Models\Asset;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class AssetExporter extends Exporter
{
    protected static ?string $model = Asset::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id'),
            ExportColumn::make('name'),
            ExportColumn::make('serial_number'),
            ExportColumn::make('room.name')->label('Room'),
            ExportColumn::make('room.branch.name')->label('Branch'),
            ExportColumn::make('category.name')->label('Category'),
            ExportColumn::make('status.name')->label('Status'),
            ExportColumn::make('qr_code'),
            ExportColumn::make('purchase_date'),
            ExportColumn::make('purchase_value'),
            ExportColumn::make('notes'),
            ExportColumn::make('created_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        return 'Assets export complete: ' . number_format($export->successful_rows) . ' rows.';
    }
}
