<?php

namespace App\Filament\Admin\Imports;

use App\Models\Branch;
use App\Models\ResponsiblePerson;
use App\Models\Room;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class RoomImporter extends Importer
{
    protected static ?string $model = Room::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')->requiredMapping()->rules(['required', 'string', 'max:255']),
            ImportColumn::make('room_number')->rules(['nullable', 'string', 'max:50']),
            ImportColumn::make('branch_id')
                ->label('Branch Name')
                ->resolveUsing(fn (string $state) => Branch::where('name', $state)
                    ->where('institution_id', app('current_institution_id'))
                    ->value('id')),
            ImportColumn::make('responsible_person_id')
                ->label('Responsible Person Name')
                ->resolveUsing(fn (string $state) => ResponsiblePerson::where('name', $state)
                    ->where('institution_id', app('current_institution_id'))
                    ->value('id')),
        ];
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        return 'Rooms import complete: ' . number_format($import->successful_rows) . ' rows.';
    }
}
