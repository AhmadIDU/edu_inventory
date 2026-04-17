<?php

namespace App\Filament\Admin\Resources\AssetResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TransfersRelationManager extends RelationManager
{
    protected static string $relationship = 'transfers';
    protected static ?string $title = 'Transfer History';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('transfer_date')->date()->sortable(),
                TextColumn::make('fromRoom.name')->label('From Room')->placeholder('Initial placement'),
                TextColumn::make('toRoom.name')->label('To Room'),
                TextColumn::make('toRoom.branch.name')->label('To Branch')->placeholder('—'),
                TextColumn::make('transferred_by')->label('Transferred By'),
                TextColumn::make('notes')->limit(50)->placeholder('—')->toggleable(),
                TextColumn::make('created_at')->dateTime()->sortable()->label('Logged At'),
            ])
            ->defaultSort('transfer_date', 'desc')
            ->paginated(false);
    }
}
