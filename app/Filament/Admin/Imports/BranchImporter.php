<?php

namespace App\Filament\Admin\Imports;

use App\Models\Branch;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class BranchImporter extends Importer
{
    protected static ?string $model = Branch::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')->requiredMapping()->rules(['required', 'string', 'max:255']),
            ImportColumn::make('address')->rules(['nullable', 'string']),
            ImportColumn::make('is_active')->boolean()->rules(['nullable', 'boolean']),
        ];
    }

    public function resolveRecord(): Branch
    {
        return new Branch();
    }

    protected function beforeSave(): void
    {
        // institution_id is auto-stamped by the model's booted() method via InstitutionScope
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        return 'Branch import complete: ' . number_format($import->successful_rows) . ' rows imported.';
    }
}
