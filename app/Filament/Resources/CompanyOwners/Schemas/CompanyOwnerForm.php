<?php

namespace App\Filament\Resources\CompanyOwners\Schemas;

use App\Models\UserCompany;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class CompanyOwnerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_company_id')
                    ->label('Kompanija')
                    ->options(UserCompany::where('user_id', Auth::id())->pluck('company_name', 'id'))
                    ->required()
                    ->columnSpanFull(),

                // Osnovno ime
                TextInput::make('first_name')
                    ->label('Ime')
                    ->required()
                    ->maxLength(255),

                TextInput::make('last_name')
                    ->label('Prezime')
                    ->required()
                    ->maxLength(255),

                TextInput::make('parent_name')
                    ->label('Ime oca/majke')
                    ->maxLength(255),

                // Lični podaci
                TextInput::make('nationality')
                    ->label('Nacionalnost')
                    ->maxLength(255),

                TextInput::make('personal_id_number')
                    ->label('JMBG')
                    ->maxLength(255),

                TextInput::make('education_level')
                    ->label('Stepen obrazovanja')
                    ->maxLength(255),

                Select::make('gender')
                    ->label('Pol')
                    ->options([
                        'male' => 'Muški',
                        'female' => 'Ženski',
                        'other' => 'Ostalo',
                    ]),

                // Adresa
                TextInput::make('city')
                    ->label('Grad')
                    ->maxLength(255),

                TextInput::make('municipality')
                    ->label('Opština')
                    ->maxLength(255),

                Textarea::make('address')
                    ->label('Adresa')
                    ->rows(2)
                    ->columnSpanFull(),

                TextInput::make('address_number')
                    ->label('Broj adrese')
                    ->maxLength(255),

                // Kontakt
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->maxLength(255),
            ])
            ->columns(2);
    }
}
