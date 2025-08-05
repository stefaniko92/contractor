<?php

namespace App\Filament\Resources\AvansnaFakturas\Tables;

use App\Filament\Resources\Invoices\Tables\InvoicesTable;
use Filament\Tables\Table;

class AvansnaFakturasTable
{
    public static function configure(Table $table): Table
    {
        // Use the same table configuration as invoices
        return InvoicesTable::configure($table);
    }
}
