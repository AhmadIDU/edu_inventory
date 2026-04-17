<?php

namespace App\Filament\Admin\Exports;

use App\Models\Room;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class RoomExporter extends Exporter
{
    protected static ?string $model = Room::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id'),
            ExportColumn::make('name'),
            ExportColumn::make('room_number'),
            ExportColumn::make('branch.name')->label('Branch'),
            ExportColumn::make('responsiblePerson.name')->label('Responsible Person'),
            ExportColumn::make('created_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        return 'Rooms export complete: ' . number_format($export->successful_rows) . ' rows.';
    }
}
