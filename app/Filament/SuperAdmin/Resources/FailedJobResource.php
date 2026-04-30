<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\FailedJobResource\Pages;
use App\Models\FailedJob;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FailedJobResource extends Resource
{
    protected static ?string $model = FailedJob::class;
    protected static string|\UnitEnum|null $navigationGroup = 'System';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static ?string $navigationLabel = 'Failed Jobs';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('display_name')
                    ->label('Job')
                    ->searchable(query: fn ($query, $search) => $query->where('payload', 'like', "%{$search}%")),
                TextColumn::make('queue')
                    ->badge()
                    ->sortable(),
                TextColumn::make('failed_at')
                    ->label('Failed At')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('exception')
                    ->label('Error')
                    ->limit(80)
                    ->tooltip(fn ($state) => $state),
            ])
            ->defaultSort('id', 'desc')
            ->actions([DeleteAction::make()])
            ->bulkActions([DeleteBulkAction::make()])
            ->emptyStateHeading('No failed jobs')
            ->emptyStateDescription('All jobs have been processed successfully.');
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListFailedJobs::route('/')];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}