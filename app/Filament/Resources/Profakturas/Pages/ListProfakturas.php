<?php

namespace App\Filament\Resources\Profakturas\Pages;

use App\Filament\Resources\Profakturas\ProfakturaResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListProfakturas extends ListRecords
{
    protected static string $resource = ProfakturaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('custom_create')
                ->label('Nova Profaktura')
                ->url(ProfakturaResource::getUrl('custom-create'))
                ->icon('heroicon-o-plus')
                ->color('primary'),
        ];
    }
}
