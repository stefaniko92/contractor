<?php

namespace App\Filament\Resources\Obligations;

use App\Filament\Resources\Obligations\Pages\CreateObligation;
use App\Filament\Resources\Obligations\Pages\EditObligation;
use App\Filament\Resources\Obligations\Pages\ListObligations;
use App\Filament\Resources\Obligations\Schemas\ObligationForm;
use App\Filament\Resources\Obligations\Tables\ObligationsTable;
use App\Models\Obligation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ObligationResource extends Resource
{
    protected static ?string $model = Obligation::class;

    protected static ?string $modelLabel = 'Zaduženje';

    protected static ?string $pluralModelLabel = 'Zaduženja';

    protected static ?int $navigationSort = 17;

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.moja_kompanija');
    }

    public static function getNavigationLabel(): string
    {
        return __('navigation.menu_items.obligations');
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ObligationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ObligationsTable::configure($table);
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
            'index' => ListObligations::route('/'),
            'create' => CreateObligation::route('/create'),
            'edit' => EditObligation::route('/{record}/edit'),
        ];
    }
}
