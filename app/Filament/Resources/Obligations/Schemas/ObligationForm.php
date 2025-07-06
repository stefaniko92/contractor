<?php

namespace App\Filament\Resources\Obligations\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ObligationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('year')
                    ->required(),
                TextInput::make('month')
                    ->required()
                    ->numeric(),
                Select::make('type')
                    ->options(['tax' => 'Tax', 'pension' => 'Pension', 'health' => 'Health'])
                    ->required(),
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
                Select::make('status')
                    ->options(['pending' => 'Pending', 'paid' => 'Paid'])
                    ->default('pending')
                    ->required(),
                DatePicker::make('payment_date'),
            ]);
    }
}
