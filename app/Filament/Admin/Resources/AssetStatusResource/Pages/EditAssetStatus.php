<?php

namespace App\Filament\Admin\Resources\AssetStatusResource\Pages;

use App\Filament\Admin\Resources\AssetStatusResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAssetStatus extends EditRecord
{
    protected static string $resource = AssetStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
