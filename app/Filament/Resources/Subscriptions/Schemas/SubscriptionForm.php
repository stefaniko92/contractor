<?php

namespace App\Filament\Resources\Subscriptions\Schemas;

use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Toggle;
use Filament\Schemas\Schema;

class SubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informacije o pretplati')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('user_id')
                                    ->label('Korisnik')
                                    ->relationship('user', 'name')
                                    ->required()
                                    ->disabled(fn (string $operation) => $operation === 'edit'),
                                TextInput::make('type')
                                    ->label('Tip pretplate')
                                    ->required(),
                                TextInput::make('stripe_id')
                                    ->label('Stripe ID')
                                    ->required()
                                    ->disabled(),
                                TextInput::make('stripe_status')
                                    ->label('Status pretplate')
                                    ->required(),
                            ]),
                        TextInput::make('stripe_price')
                            ->label('Stripe Price ID')
                            ->nullable(),
                        Toggle::make('is_active')
                            ->label('Aktivna')
                            ->default(true),
                    ]),
            ]);
    }
}
