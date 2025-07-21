<?php

namespace App\Filament\Resources\CompanyOwners;

use App\Filament\Resources\CompanyOwners\Pages\CreateCompanyOwner;
use App\Filament\Resources\CompanyOwners\Pages\EditCompanyOwner;
use App\Filament\Resources\CompanyOwners\Pages\ListCompanyOwners;
use App\Filament\Resources\CompanyOwners\Schemas\CompanyOwnerForm;
use App\Filament\Resources\CompanyOwners\Tables\CompanyOwnersTable;
use App\Models\CompanyOwner;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CompanyOwnerResource extends Resource
{
    protected static ?string $model = CompanyOwner::class;

    protected static ?string $navigationLabel = 'Vlasnik kompanije';

    protected static ?string $modelLabel = 'Vlasnik kompanije';

    protected static ?string $pluralModelLabel = 'Vlasnici kompanija';

    protected static bool $shouldRegisterNavigation = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUser;

    public static function form(Schema $schema): Schema
    {
        return CompanyOwnerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CompanyOwnersTable::configure($table);
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
            'index' => ListCompanyOwners::route('/'),
            'create' => CreateCompanyOwner::route('/create'),
            'edit' => EditCompanyOwner::route('/{record}/edit'),
        ];
    }
}
