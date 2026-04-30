<?php

namespace App\Filament\Admin\Imports;

use App\Models\AssetStatus;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class AssetStatusImporter extends Importer
{
    protected static ?string $model = AssetStatus::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')->requiredMapping()->rules(['required', 'string', 'max:100']),
            ImportColumn::make('color')->rules(['nullable', 'string', 'max:7']),
        ];
    }

    public function resolveRecord(): AssetStatus
    {
        return new AssetStatus();
    }

    protected function beforeSave(): void
    {
        // Never allow importing system statuses — custom only
        $this->data['is_system'] = false;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        return 'Statuses import complete: ' . number_format($import->successful_rows) . ' rows.';
    }
}
