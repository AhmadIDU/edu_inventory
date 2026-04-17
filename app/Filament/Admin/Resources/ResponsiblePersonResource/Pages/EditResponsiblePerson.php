<?php

namespace App\Filament\Admin\Resources\ResponsiblePersonResource\Pages;

use App\Filament\Admin\Resources\ResponsiblePersonResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditResponsiblePerson extends EditRecord
{
    protected static string $resource = ResponsiblePersonResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
