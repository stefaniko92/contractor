<?php

namespace App\Filament\Resources\Profakturas\Tables;

use App\Filament\Resources\Invoices\Tables\InvoicesTable;
use Filament\Tables\Table;

class ProfakturasTable
{
    public static function configure(Table $table): Table
    {
        // Use the same table configuration as invoices
        return InvoicesTable::configure($table);
    }
}
