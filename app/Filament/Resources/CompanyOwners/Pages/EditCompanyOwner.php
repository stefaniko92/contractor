<?php

namespace App\Filament\Resources\CompanyOwners\Pages;

use App\Filament\Resources\CompanyOwners\CompanyOwnerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCompanyOwner extends EditRecord
{
    protected static string $resource = CompanyOwnerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
