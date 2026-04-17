<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AssetTransferResource\Pages;
use App\Models\AssetTransfer;
use Filament\Actions\ExportAction;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AssetTransferResource extends Resource
{
    protected static ?string $model = AssetTransfer::class;
    protected static string | \UnitEnum | null $navigationGroup = 'Assets';
    protected static ?string $label = 'Transfer Log';

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('transfer_date')->date()->sortable(),
                TextColumn::make('asset.name')->label('Asset')->searchable()->sortable(),
                TextColumn::make('fromRoom.name')->label('From Room')->placeholder('—'),
                TextColumn::make('toRoom.name')->label('To Room'),
                TextColumn::make('toRoom.branch.name')->label('Branch')->placeholder('—'),
                TextColumn::make('transferred_by')->label('By')->searchable(),
                TextColumn::make('notes')->limit(40)->placeholder('—')->toggleable(),
                TextColumn::make('created_at')->dateTime()->sortable()->label('Logged')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('transfer_date', 'desc')
            ->filters([
                Filter::make('this_month')
                    ->label('This Month')
                    ->query(fn (Builder $q) => $q->whereMonth('transfer_date', now()->month)),
            ])
            ->headerActions([
                ExportAction::make()->exporter(\App\Filament\Admin\Exports\AssetTransferExporter::class),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssetTransfers::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Transfers are created via the Asset view page
    }
}
