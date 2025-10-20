<?php

namespace App\Filament\Resources\UserCompanies\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class UserCompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('user_id')
                    ->default(Auth::id()),

                // Osnovni podaci kompanije
                TextInput::make('company_name')
                    ->label('Naziv kompanije')
                    ->required()
                    ->maxLength(255),

                TextInput::make('company_full_name')
                    ->label('Pun naziv kompanije')
                    ->maxLength(255),

                TextInput::make('company_tax_id')
                    ->label('PIB')
                    ->maxLength(255),

                TextInput::make('company_registry_number')
                    ->label('Matični broj')
                    ->maxLength(255),

                // Delatnost
                TextInput::make('company_activity_code')
                    ->label('Šifra delatnosti')
                    ->maxLength(255),

                Textarea::make('company_activity_desc')
                    ->label('Opis delatnosti')
                    ->rows(3)
                    ->columnSpanFull(),

                DatePicker::make('company_registration_date')
                    ->label('Datum registracije')
                    ->displayFormat('d.m.Y'),

                TextInput::make('company_status')
                    ->label('Status kompanije')
                    ->maxLength(255),

                // Adresa
                TextInput::make('company_city')
                    ->label('Grad')
                    ->maxLength(255),

                TextInput::make('company_municipality')
                    ->label('Opština')
                    ->maxLength(255),

                TextInput::make('company_postal_code')
                    ->label('Poštanski broj')
                    ->maxLength(255),

                Textarea::make('company_address')
                    ->label('Adresa')
                    ->rows(2)
                    ->columnSpanFull(),

                TextInput::make('company_address_number')
                    ->label('Broj adrese')
                    ->maxLength(255),

                // Kontakt
                TextInput::make('company_phone')
                    ->label('Telefon')
                    ->tel()
                    ->maxLength(255),

                TextInput::make('company_email')
                    ->label('Email')
                    ->email()
                    ->maxLength(255),

                Toggle::make('show_email_on_invoice')
                    ->label('Prikaži email na fakturi')
                    ->default(true),

                // Strani račun
                TextInput::make('company_foreign_account_number')
                    ->label('Broj stranog računa')
                    ->maxLength(255),

                TextInput::make('company_foreign_account_bank')
                    ->label('Banka stranog računa')
                    ->maxLength(255),

                // Logo
                FileUpload::make('company_logo_path')
                    ->label('Logo kompanije')
                    ->image()
                    ->disk('s3')
                    ->visibility('private')
                    ->directory('company-logos')
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
}
