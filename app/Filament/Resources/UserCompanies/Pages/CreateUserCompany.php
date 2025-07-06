<?php

namespace App\Filament\Resources\UserCompanies\Pages;

use App\Filament\Resources\UserCompanies\UserCompanyResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUserCompany extends CreateRecord
{
    protected static string $resource = UserCompanyResource::class;
}
