<?php

namespace App\Filament\Resources\AvansnaFakturas\Pages;

use App\Filament\Resources\AvansnaFakturas\AvansnaFakturaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAvansnaFaktura extends EditRecord
{
    protected static string $resource = AvansnaFakturaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
