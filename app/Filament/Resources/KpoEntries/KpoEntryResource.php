<?php

namespace App\Filament\Resources\KpoEntries;

use App\Filament\Resources\KpoEntries\Pages\CreateKpoEntry;
use App\Filament\Resources\KpoEntries\Pages\EditKpoEntry;
use App\Filament\Resources\KpoEntries\Pages\ListKpoEntries;
use App\Filament\Resources\KpoEntries\Schemas\KpoEntryForm;
use App\Filament\Resources\KpoEntries\Tables\KpoEntriesTable;
use App\Models\KpoEntry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class KpoEntryResource extends Resource
{
    protected static ?string $model = KpoEntry::class;

    protected static ?string $modelLabel = 'KPO Unos';

    protected static ?string $pluralModelLabel = 'KPO Unosi';

    protected static bool $shouldRegisterNavigation = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTableCells;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }

    public static function form(Schema $schema): Schema
    {
        return KpoEntryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KpoEntriesTable::configure($table);
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
            'index' => ListKpoEntries::route('/'),
            'create' => CreateKpoEntry::route('/create'),
            'edit' => EditKpoEntry::route('/{record}/edit'),
        ];
    }
}
