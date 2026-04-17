<?php

namespace App\Filament\Admin\Imports;

use App\Models\AssetCategory;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class AssetCategoryImporter extends Importer
{
    protected static ?string $model = AssetCategory::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')->requiredMapping()->rules(['required', 'string', 'max:100']),
            ImportColumn::make('description')->rules(['nullable', 'string']),
        ];
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        return 'Categories import complete: ' . number_format($import->successful_rows) . ' rows.';
    }
}
