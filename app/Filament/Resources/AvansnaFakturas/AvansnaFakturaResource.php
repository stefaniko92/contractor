<?php

namespace App\Filament\Resources\AvansnaFakturas;

use App\Filament\Resources\AvansnaFakturas\Pages\CreateAvansnaFaktura;
use App\Filament\Resources\AvansnaFakturas\Pages\CustomCreateAvansnaFaktura;
use App\Filament\Resources\AvansnaFakturas\Pages\EditAvansnaFaktura;
use App\Filament\Resources\AvansnaFakturas\Pages\ListAvansnaFakturas;
use App\Filament\Resources\AvansnaFakturas\Schemas\AvansnaFakturaForm;
use App\Filament\Resources\AvansnaFakturas\Tables\AvansnaFakturasTable;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Builder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AvansnaFakturaResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Fakturisanje';

    protected static ?string $navigationLabel = 'Avansne fakture';

    protected static ?string $modelLabel = 'Avansna faktura';

    protected static ?string $pluralModelLabel = 'Avansne fakture';

    protected static ?int $navigationSort = 12;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('invoice_document_type', 'avansna_faktura');
    }

    public static function form(Schema $schema): Schema
    {
        return AvansnaFakturaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AvansnaFakturasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAvansnaFakturas::route('/'),
            'create' => CreateAvansnaFaktura::route('/create'),
            'custom-create' => CustomCreateAvansnaFaktura::route('/custom-create'),
            'edit' => EditAvansnaFaktura::route('/{record}/edit'),
        ];
    }
}
