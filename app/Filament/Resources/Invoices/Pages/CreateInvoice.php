<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Filament\Resources\Invoices\InvoiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
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
}
