<?php

namespace App\Filament\SuperAdmin\Resources\InstitutionResource\Pages;

use App\Filament\SuperAdmin\Resources\InstitutionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInstitutions extends ListRecords
{
    protected static string $resource = InstitutionResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
