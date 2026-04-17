<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AssetCategoryResource\Pages;
use App\Models\AssetCategory;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AssetCategoryResource extends Resource
{
    protected static ?string $model = AssetCategory::class;
    protected static string | \UnitEnum | null $navigationGroup = 'Asset Settings';
    protected static ?string $label = 'Category';

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema->components([
            Section::make()->schema([
                TextInput::make('name')->required()->maxLength(100),
                Textarea::make('description')->rows(3),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('description')->limit(60)->toggleable(),
                TextColumn::make('assets_count')->counts('assets')->label('Assets')->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                ExportAction::make()->exporter(\App\Filament\Admin\Exports\AssetCategoryExporter::class),
                ImportAction::make()->importer(\App\Filament\Admin\Imports\AssetCategoryImporter::class),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAssetCategories::route('/'),
            'create' => Pages\CreateAssetCategory::route('/create'),
            'edit'   => Pages\EditAssetCategory::route('/{record}/edit'),
        ];
    }
}
