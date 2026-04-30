<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\JobResource\Pages;
use App\Models\Job;
use Carbon\Carbon;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class JobResource extends Resource
{
    protected static ?string $model = Job::class;
    protected static string|\UnitEnum|null $navigationGroup = 'System';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-queue-list';
    protected static ?string $navigationLabel = 'Queue Jobs';

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
                TextColumn::make('attempts')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => $state === 'Processing' ? 'warning' : 'success'),
                TextColumn::make('available_at')
                    ->label('Available At')
                    ->formatStateUsing(fn ($state) => $state ? Carbon::createFromTimestamp($state)->diffForHumans() : '-'),
                TextColumn::make('created_at')
                    ->label('Queued At')
                    ->formatStateUsing(fn ($state) => $state ? Carbon::createFromTimestamp($state)->diffForHumans() : '-')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->actions([DeleteAction::make()])
            ->bulkActions([DeleteBulkAction::make()])
            ->emptyStateHeading('No pending jobs')
            ->emptyStateDescription('The queue is empty.');
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListJobs::route('/')];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}