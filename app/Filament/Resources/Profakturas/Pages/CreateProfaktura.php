<?php

namespace App\Filament\Resources\Profakturas\Pages;

use App\Filament\Resources\Profakturas\ProfakturaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProfaktura extends CreateRecord
{
    protected static string $resource = ProfakturaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set document type to profaktura
        $data['invoice_document_type'] = 'profaktura';

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
}
