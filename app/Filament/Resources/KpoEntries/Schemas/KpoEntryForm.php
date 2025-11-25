<?php

namespace App\Filament\Resources\KpoEntries\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class KpoEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('kpo_upload_id')
                    ->relationship('kpoUpload', 'id')
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('client_id')
                    ->relationship('client', 'id'),
                Select::make('invoice_id')
                    ->relationship('invoice', 'id'),
                TextInput::make('entry_number')
                    ->numeric(),
                DatePicker::make('date'),
                TextInput::make('invoice_mark'),
                Textarea::make('product_service_description')
                    ->columnSpanFull(),
                TextInput::make('client_name'),
                TextInput::make('income_amount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('expense_amount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('currency')
                    ->required()
                    ->default('RSD'),
                TextInput::make('raw_data'),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
