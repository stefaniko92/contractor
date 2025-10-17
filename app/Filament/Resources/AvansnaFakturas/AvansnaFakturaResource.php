<?php

namespace App\Filament\Resources\AvansnaFakturas;

use App\Filament\Resources\AvansnaFakturas\Pages\CreateAvansnaFaktura;
use App\Filament\Resources\AvansnaFakturas\Pages\CustomCreateAvansnaFaktura;
use App\Filament\Resources\AvansnaFakturas\Pages\EditAvansnaFaktura;
use App\Filament\Resources\AvansnaFakturas\Pages\ListAvansnaFakturas;
use App\Filament\Resources\AvansnaFakturas\Schemas\AvansnaFakturaForm;
use App\Filament\Resources\AvansnaFakturas\Tables\AvansnaFakturasTable;
use App\Models\Invoice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AvansnaFakturaResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $modelLabel = 'Avansna faktura';

    protected static ?string $pluralModelLabel = 'Avansne fakture';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.fakturisanje');
    }

    public static function getNavigationLabel(): string
    {
        return __('navigation.menu_items.avansi');
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('invoice_document_type', 'avansna_faktura')
            ->where('user_id', auth()->id());
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
