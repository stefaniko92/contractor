<?php

namespace App\Filament\Resources\UserCompanies\Pages;

use App\Filament\Resources\UserCompanies\UserCompanyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUserCompanies extends ListRecords
{
    protected static string $resource = UserCompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
