<?php

namespace App\Filament\Admin\Imports;

use App\Models\ResponsiblePerson;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class ResponsiblePersonImporter extends Importer
{
    protected static ?string $model = ResponsiblePerson::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')->requiredMapping()->rules(['required', 'string', 'max:255']),
            ImportColumn::make('contact')->rules(['nullable', 'string', 'max:255']),
            ImportColumn::make('position')->rules(['nullable', 'string', 'max:255']),
        ];
    }

    public function resolveRecord(): ResponsiblePerson
    {
        return new ResponsiblePerson();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        return 'Responsible persons import complete: ' . number_format($import->successful_rows) . ' rows.';
    }
}
