<?php

namespace App\Filament\Resources\AvansnaFakturas\Pages;

use App\Filament\Resources\AvansnaFakturas\AvansnaFakturaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAvansnaFaktura extends EditRecord
{
    protected static string $resource = AvansnaFakturaResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Calculate total amount from invoice items
        $totalAmount = 0;
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                if (isset($item['amount']) && is_numeric($item['amount'])) {
                    $totalAmount += (float) $item['amount'];
                }
            }
        }
        
        $data['amount'] = $totalAmount;
        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
