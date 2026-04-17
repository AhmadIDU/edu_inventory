<?php

namespace App\Filament\Admin\Exports;

use App\Models\ResponsiblePerson;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ResponsiblePersonExporter extends Exporter
{
    protected static ?string $model = ResponsiblePerson::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id'),
            ExportColumn::make('name'),
            ExportColumn::make('contact'),
            ExportColumn::make('position'),
            ExportColumn::make('created_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        return 'Responsible persons export complete: ' . number_format($export->successful_rows) . ' rows.';
    }
}
