<?php

namespace App\Filament\Resources\AvansnaFakturas\Pages;

use App\Filament\Resources\AvansnaFakturas\AvansnaFakturaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAvansnaFaktura extends CreateRecord
{
    protected static string $resource = AvansnaFakturaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set document type to avansna_faktura
        $data['invoice_document_type'] = 'avansna_faktura';
        
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
