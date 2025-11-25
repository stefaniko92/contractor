<?php

namespace App\Filament\Resources\KpoEntries\Pages;

use App\Filament\Resources\KpoEntries\KpoEntryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditKpoEntry extends EditRecord
{
    protected static string $resource = KpoEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
