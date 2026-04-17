<?php

namespace App\Filament\Admin\Exports;

use App\Models\AssetTransfer;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class AssetTransferExporter extends Exporter
{
    protected static ?string $model = AssetTransfer::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id'),
            ExportColumn::make('asset.name')->label('Asset'),
            ExportColumn::make('fromRoom.name')->label('From Room'),
            ExportColumn::make('toRoom.name')->label('To Room'),
            ExportColumn::make('toRoom.branch.name')->label('To Branch'),
            ExportColumn::make('transferred_by'),
            ExportColumn::make('transfer_date'),
            ExportColumn::make('notes'),
            ExportColumn::make('created_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        return 'Transfer log export complete: ' . number_format($export->successful_rows) . ' rows.';
    }
}
