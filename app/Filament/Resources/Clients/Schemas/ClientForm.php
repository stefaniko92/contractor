<?php

namespace App\Filament\Resources\Clients\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('user_id')
                    ->default(Auth::id()),

                Section::make('Tip klijenta')
                    ->schema([
                        Radio::make('is_domestic')
                            ->label('Tip klijenta')
                            ->options([
                                1 => 'Domaći klijent',
                                0 => 'Strani klijent',
                            ])
                            ->default(1)
                            ->required()
                            ->live(),
                    ]),

                Section::make('Osnovne informacije')
                    ->schema([
                        TextInput::make('company_name')
                            ->label('Naziv kompanije')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('tax_id')
                            ->label('PIB')
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),

                        TextInput::make('phone')
                            ->label('Telefon')
                            ->tel()
                            ->maxLength(255),

                        Textarea::make('address')
                            ->label('Adresa')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Informacije za strane klijente')
                    ->schema([
                        TextInput::make('city')
                            ->label('Grad')
                            ->maxLength(255),

                        TextInput::make('country')
                            ->label('Zemlja')
                            ->maxLength(255),

                        TextInput::make('vat_number')
                            ->label('VAT/EIB broj')
                            ->maxLength(255),

                        TextInput::make('registration_number')
                            ->label('ID/MB broj')
                            ->maxLength(255),
                    ])
                    ->columns(2)
                    ->visible(fn ($get) => $get('is_domestic') === 0),

                Section::make('Dodatne informacije')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Napomene')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ])
            ->columns(2);
    }
}
