<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AssetResource\Pages;
use App\Filament\Admin\Resources\AssetResource\RelationManagers\TransfersRelationManager;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetStatus;
use App\Models\Room;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AssetResource extends Resource
{
    protected static ?string $model = Asset::class;
    protected static string | \UnitEnum | null $navigationGroup = 'Assets';

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema->components([
            Section::make('Asset Details')->schema([
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('serial_number')->maxLength(255)->label('Serial Number'),
                Select::make('room_id')
                    ->label('Room')
                    ->options(fn () => Room::with('branch')->get()->mapWithKeys(
                        fn ($room) => [$room->id => $room->display_name]
                    ))
                    ->searchable()
                    ->required(),
                Select::make('category_id')
                    ->label('Category')
                    ->options(fn () => AssetCategory::pluck('name', 'id'))
                    ->searchable()
                    ->nullable(),
                Select::make('status_id')
                    ->label('Status')
                    ->options(fn () => AssetStatus::pluck('name', 'id'))
                    ->searchable()
                    ->required(),
            ])->columns(2),

            Section::make('Purchase Info')->schema([
                DatePicker::make('purchase_date')->label('Purchase Date'),
                TextInput::make('purchase_value')
                    ->label('Purchase Value')
                    ->numeric()
                    ->prefix('$'),
                Textarea::make('notes')->rows(3)->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('serial_number')->label('Serial No.')->searchable()->toggleable(),
                TextColumn::make('room.name')->label('Room')->sortable(),
                TextColumn::make('room.branch.name')->label('Branch')->sortable()->placeholder('—'),
                TextColumn::make('category.name')->label('Category')->sortable()->placeholder('—'),
                TextColumn::make('status.name')
                    ->label('Status')
                    ->badge()
                    ->color(fn (Asset $record): string => match (true) {
                        str_contains(strtolower($record->status?->name ?? ''), 'active') => 'success',
                        str_contains(strtolower($record->status?->name ?? ''), 'maintenance') => 'warning',
                        str_contains(strtolower($record->status?->name ?? ''), 'dispos') => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('purchase_date')->date()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status_id')
                    ->label('Status')
                    ->options(fn () => AssetStatus::pluck('name', 'id'))
                    ->searchable(),
                SelectFilter::make('category_id')
                    ->label('Category')
                    ->options(fn () => AssetCategory::pluck('name', 'id'))
                    ->searchable(),
            ])
            ->headerActions([
                ExportAction::make()->exporter(\App\Filament\Admin\Exports\AssetExporter::class),
                ImportAction::make()->importer(\App\Filament\Admin\Imports\AssetImporter::class),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            TransfersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAssets::route('/'),
            'create' => Pages\CreateAsset::route('/create'),
            'edit'   => Pages\EditAsset::route('/{record}/edit'),
            'view'   => Pages\ViewAsset::route('/{record}'),
        ];
    }
}
