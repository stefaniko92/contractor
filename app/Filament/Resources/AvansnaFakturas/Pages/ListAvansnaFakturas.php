<?php

namespace App\Filament\Resources\AvansnaFakturas\Pages;

use App\Filament\Resources\AvansnaFakturas\AvansnaFakturaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAvansnaFakturas extends ListRecords
{
    protected static string $resource = AvansnaFakturaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
