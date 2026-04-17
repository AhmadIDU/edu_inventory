<?php

namespace App\Filament\Admin\Resources\AssetResource\Pages;

use App\Filament\Admin\Resources\AssetResource;
use App\Models\AssetStatus;
use App\Models\Room;
use App\Services\TransferService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewAsset extends ViewRecord
{
    protected static string $resource = AssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),

            Action::make('transfer')
                ->label('Transfer Asset')
                ->icon('heroicon-o-arrows-right-left')
                ->color('warning')
                ->form([
                    Select::make('to_room_id')
                        ->label('Destination Room')
                        ->options(fn () => Room::with('branch')->get()->mapWithKeys(
                            fn ($room) => [$room->id => $room->display_name]
                        ))
                        ->searchable()
                        ->required(),
                    TextInput::make('transferred_by')
                        ->label('Transferred By')
                        ->required(),
                    DatePicker::make('transfer_date')
                        ->label('Transfer Date')
                        ->default(now())
                        ->required(),
                    TextInput::make('notes')->label('Notes'),
                ])
                ->action(function (array $data) {
                    app(TransferService::class)->transfer(
                        $this->record,
                        $data['to_room_id'],
                        $data['transferred_by'],
                        $data['transfer_date'],
                        $data['notes'] ?? null
                    );

                    Notification::make()
                        ->title('Asset transferred successfully')
                        ->success()
                        ->send();

                    $this->refreshFormData(['room_id']);
                }),

            Action::make('download_qr')
                ->label('Download QR')
                ->icon('heroicon-o-qr-code')
                ->color('gray')
                ->url(fn () => route('asset.qr.download', $this->record->qr_code))
                ->openUrlInNewTab(),

            Action::make('print_label')
                ->label('Print Label')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn () => route('asset.label', $this->record->qr_code))
                ->openUrlInNewTab(),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [];
    }
}
