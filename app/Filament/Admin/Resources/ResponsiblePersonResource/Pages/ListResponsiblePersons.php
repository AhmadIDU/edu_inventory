<?php

namespace App\Filament\Admin\Resources\ResponsiblePersonResource\Pages;

use App\Filament\Admin\Resources\ResponsiblePersonResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListResponsiblePersons extends ListRecords
{
    protected static string $resource = ResponsiblePersonResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
