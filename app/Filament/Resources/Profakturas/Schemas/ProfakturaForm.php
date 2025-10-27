<?php

namespace App\Filament\Resources\Profakturas\Schemas;

use App\Filament\Resources\Invoices\Schemas\InvoiceForm;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Schema;

class ProfakturaForm
{
    public static function configure(Schema $schema): Schema
    {
        // Use the same form as invoices but set the document type to profaktura
        $schema = InvoiceForm::configure($schema);

        // Add hidden field to force profaktura type
        $components = $schema->getComponents();
        array_unshift($components,
            Hidden::make('invoice_document_type')->default('profaktura')
        );

        return $schema->components($components);
    }
}
