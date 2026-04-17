<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AssetStatusResource\Pages;
use App\Models\AssetStatus;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AssetStatusResource extends Resource
{
    protected static ?string $model = AssetStatus::class;
    protected static string | \UnitEnum | null $navigationGroup = 'Asset Settings';
    protected static ?string $label = 'Asset Status';

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema->components([
            Section::make()->schema([
                TextInput::make('name')->required()->maxLength(100),
                ColorPicker::make('color')->default('#6b7280'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ColorColumn::make('color'),
                TextColumn::make('name')->searchable()->sortable(),
                IconColumn::make('is_system')->boolean()->label('System Default'),
                TextColumn::make('assets_count')->counts('assets')->label('Assets')->sortable(),
            ])
            ->headerActions([
                ExportAction::make()->exporter(\App\Filament\Admin\Exports\AssetStatusExporter::class),
                ImportAction::make()->importer(\App\Filament\Admin\Imports\AssetStatusImporter::class),
            ])
            ->actions([
                EditAction::make()->visible(fn (AssetStatus $record) => ! $record->is_system),
                DeleteAction::make()->visible(fn (AssetStatus $record) => ! $record->is_system),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAssetStatuses::route('/'),
            'create' => Pages\CreateAssetStatus::route('/create'),
            'edit'   => Pages\EditAssetStatus::route('/{record}/edit'),
        ];
    }
}
