<?php

namespace App\Filament\Admin\Imports;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetStatus;
use App\Models\Room;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Str;

class AssetImporter extends Importer
{
    protected static ?string $model = Asset::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')->requiredMapping()->rules(['required', 'string', 'max:255']),
            ImportColumn::make('serial_number')->rules(['nullable', 'string', 'max:255']),
            ImportColumn::make('room_id')
                ->label('Room Name')
                ->requiredMapping()
                ->castStateUsing(fn (string $state) => Room::where('name', $state)
                    ->where('institution_id', app('current_institution_id'))
                    ->value('id')),
            ImportColumn::make('category_id')
                ->label('Category Name')
                ->castStateUsing(fn (string $state) => AssetCategory::where('name', $state)
                    ->where('institution_id', app('current_institution_id'))
                    ->value('id')),
            ImportColumn::make('status_id')
                ->label('Status Name')
                ->requiredMapping()
                ->castStateUsing(fn (string $state) => AssetStatus::withoutGlobalScopes()
                    ->where('name', $state)
                    ->where(fn ($q) => $q->whereNull('institution_id')
                        ->orWhere('institution_id', app('current_institution_id')))
                    ->value('id')),
            ImportColumn::make('purchase_date')->rules(['nullable', 'date']),
            ImportColumn::make('purchase_value')->numeric()->rules(['nullable', 'numeric']),
            ImportColumn::make('notes')->rules(['nullable', 'string']),
        ];
    }

    public function resolveRecord(): Asset
    {
        return new Asset();
    }

    protected function beforeSave(): void
    {
        if (empty($this->data['qr_code'])) {
            $this->data['qr_code'] = Str::uuid()->toString();
        }
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        return 'Assets import complete: ' . number_format($import->successful_rows) . ' rows.';
    }
}
