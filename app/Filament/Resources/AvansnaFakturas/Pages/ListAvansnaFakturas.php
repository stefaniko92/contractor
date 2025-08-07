<?php

namespace App\Filament\Resources\AvansnaFakturas\Pages;

use App\Filament\Resources\AvansnaFakturas\AvansnaFakturaResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAvansnaFakturas extends ListRecords
{
    protected static string $resource = AvansnaFakturaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('custom_create')
                ->label('Nova Avansna Faktura')
                ->url(AvansnaFakturaResource::getUrl('custom-create'))
                ->icon('heroicon-o-plus')
                ->color('primary'),
        ];
    }
}
