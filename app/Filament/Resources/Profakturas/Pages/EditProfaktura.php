<?php

namespace App\Filament\Resources\Profakturas\Pages;

use App\Filament\Resources\Profakturas\ProfakturaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProfaktura extends EditRecord
{
    protected static string $resource = ProfakturaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
