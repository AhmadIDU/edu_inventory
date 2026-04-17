<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\RoomResource\Pages;
use App\Models\Branch;
use App\Models\ResponsiblePerson;
use App\Models\Room;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RoomResource extends Resource
{
    protected static ?string $model = Room::class;
    protected static string | \UnitEnum | null $navigationGroup = 'Organization';

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema->components([
            Section::make('Room Details')->schema([
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('room_number')->maxLength(50)->label('Room Number'),
                Select::make('branch_id')
                    ->label('Branch')
                    ->options(fn () => Branch::pluck('name', 'id'))
                    ->searchable()
                    ->placeholder('No branch (standalone room)')
                    ->nullable(),
                Select::make('responsible_person_id')
                    ->label('Responsible Person')
                    ->options(fn () => ResponsiblePerson::pluck('name', 'id'))
                    ->searchable()
                    ->nullable(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('room_number')->label('Number')->toggleable(),
                TextColumn::make('branch.name')->label('Branch')->sortable()->placeholder('—'),
                TextColumn::make('responsiblePerson.name')->label('Responsible Person')->placeholder('—'),
                TextColumn::make('assets_count')->counts('assets')->label('Assets')->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('branch_id')
                    ->label('Branch')
                    ->options(fn () => Branch::pluck('name', 'id'))
                    ->searchable(),
            ])
            ->headerActions([
                ExportAction::make()->exporter(\App\Filament\Admin\Exports\RoomExporter::class),
                ImportAction::make()->importer(\App\Filament\Admin\Imports\RoomImporter::class),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListRooms::route('/'),
            'create' => Pages\CreateRoom::route('/create'),
            'edit'   => Pages\EditRoom::route('/{record}/edit'),
        ];
    }
}
