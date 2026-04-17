<?php

namespace App\Filament\Admin\Resources\AssetStatusResource\Pages;

use App\Filament\Admin\Resources\AssetStatusResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAssetStatuses extends ListRecords
{
    protected static string $resource = AssetStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
