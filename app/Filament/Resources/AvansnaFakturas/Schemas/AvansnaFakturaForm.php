<?php

namespace App\Filament\Resources\AvansnaFakturas\Schemas;

use App\Filament\Resources\Invoices\Schemas\InvoiceForm;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Schema;

class AvansnaFakturaForm
{
    public static function configure(Schema $schema): Schema
    {
        // Use the same form as invoices but set the document type to avansna_faktura
        $schema = InvoiceForm::configure($schema);
        
        // Add hidden field to force avansna_faktura type
        $components = $schema->getComponents();
        array_unshift($components, 
            Hidden::make('invoice_document_type')->default('avansna_faktura')
        );
        
        return $schema->components($components);
    }
}
