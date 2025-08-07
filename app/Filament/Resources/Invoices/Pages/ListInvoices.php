<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Filament\Resources\Invoices\InvoiceResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('custom-create')
                ->label('Nova faktura')
                ->url(fn (): string => '/admin/create-invoice-page')
                ->icon('heroicon-o-plus')
                ->color('primary')
        ];
    }
}
