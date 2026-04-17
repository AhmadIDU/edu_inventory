<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ResponsiblePersonResource\Pages;
use App\Models\ResponsiblePerson;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ResponsiblePersonResource extends Resource
{
    protected static ?string $model = ResponsiblePerson::class;
    protected static string | \UnitEnum | null $navigationGroup = 'Organization';
    protected static ?string $label = 'Responsible Person';

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema->components([
            Section::make('Person Details')->schema([
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('contact')->maxLength(255)->helperText('Phone or email'),
                TextInput::make('position')->maxLength(255),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('contact')->searchable(),
                TextColumn::make('position')->searchable()->toggleable(),
                TextColumn::make('rooms_count')->counts('rooms')->label('Rooms'),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                ExportAction::make()->exporter(\App\Filament\Admin\Exports\ResponsiblePersonExporter::class),
                ImportAction::make()->importer(\App\Filament\Admin\Imports\ResponsiblePersonImporter::class),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListResponsiblePersons::route('/'),
            'create' => Pages\CreateResponsiblePerson::route('/create'),
            'edit'   => Pages\EditResponsiblePerson::route('/{record}/edit'),
        ];
    }
}
