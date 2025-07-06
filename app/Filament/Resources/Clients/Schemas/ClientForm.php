<?php

namespace App\Filament\Resources\Clients\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
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
                    
                Textarea::make('notes')
                    ->label('Napomene')
                    ->rows(4)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
}
