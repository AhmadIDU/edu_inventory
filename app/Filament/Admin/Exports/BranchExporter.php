<?php

namespace App\Filament\Admin\Exports;

use App\Models\Branch;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class BranchExporter extends Exporter
{
    protected static ?string $model = Branch::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id'),
            ExportColumn::make('name'),
            ExportColumn::make('address'),
            ExportColumn::make('is_active'),
            ExportColumn::make('created_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        return 'Branch export complete: ' . number_format($export->successful_rows) . ' rows exported.';
    }
}
