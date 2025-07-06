<?php

namespace App\Filament\Resources\UserCompanies;

use App\Filament\Resources\UserCompanies\Pages\CreateUserCompany;
use App\Filament\Resources\UserCompanies\Pages\EditUserCompany;
use App\Filament\Resources\UserCompanies\Pages\ListUserCompanies;
use App\Filament\Resources\UserCompanies\Schemas\UserCompanyForm;
use App\Filament\Resources\UserCompanies\Tables\UserCompaniesTable;
use App\Models\UserCompany;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserCompanyResource extends Resource
{
    protected static ?string $model = UserCompany::class;

    protected static ?string $navigationLabel = 'Profil kompanije';
    
    protected static ?string $modelLabel = 'Profil kompanije';
    
    protected static ?string $pluralModelLabel = 'Profili kompanija';
    
    protected static bool $shouldRegisterNavigation = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return UserCompanyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserCompaniesTable::configure($table);
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
            'index' => ListUserCompanies::route('/'),
            'create' => CreateUserCompany::route('/create'),
            'edit' => EditUserCompany::route('/{record}/edit'),
        ];
    }
}
