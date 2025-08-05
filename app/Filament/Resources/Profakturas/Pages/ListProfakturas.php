<?php

namespace App\Filament\Resources\Profakturas\Pages;

use App\Filament\Resources\Profakturas\ProfakturaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProfakturas extends ListRecords
{
    protected static string $resource = ProfakturaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
