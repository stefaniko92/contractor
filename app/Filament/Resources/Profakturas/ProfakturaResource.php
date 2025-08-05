<?php

namespace App\Filament\Resources\Profakturas;

use App\Filament\Resources\Profakturas\Pages\CreateProfaktura;
use App\Filament\Resources\Profakturas\Pages\EditProfaktura;
use App\Filament\Resources\Profakturas\Pages\ListProfakturas;
use App\Filament\Resources\Profakturas\Schemas\ProfakturaForm;
use App\Filament\Resources\Profakturas\Tables\ProfakturasTable;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Builder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProfakturaResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Fakturisanje';

    protected static ?string $navigationLabel = 'Profakture';

    protected static ?string $modelLabel = 'Profaktura';

    protected static ?string $pluralModelLabel = 'Profakture';

    protected static ?int $navigationSort = 11;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('invoice_document_type', 'profaktura');
    }

    public static function form(Schema $schema): Schema
    {
        return ProfakturaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProfakturasTable::configure($table);
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
            'index' => ListProfakturas::route('/'),
            'create' => CreateProfaktura::route('/create'),
            'edit' => EditProfaktura::route('/{record}/edit'),
        ];
    }
}
