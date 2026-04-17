<?php

namespace App\Filament\SuperAdmin\Resources\InstitutionResource\Pages;

use App\Filament\SuperAdmin\Resources\InstitutionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInstitution extends EditRecord
{
    protected static string $resource = InstitutionResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
