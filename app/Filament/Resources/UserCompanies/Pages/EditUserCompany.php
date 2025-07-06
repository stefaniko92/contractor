<?php

namespace App\Filament\Resources\UserCompanies\Pages;

use App\Filament\Resources\UserCompanies\UserCompanyResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUserCompany extends EditRecord
{
    protected static string $resource = UserCompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
