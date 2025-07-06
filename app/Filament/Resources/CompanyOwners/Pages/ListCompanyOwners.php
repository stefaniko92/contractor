<?php

namespace App\Filament\Resources\CompanyOwners\Pages;

use App\Filament\Resources\CompanyOwners\CompanyOwnerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCompanyOwners extends ListRecords
{
    protected static string $resource = CompanyOwnerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
