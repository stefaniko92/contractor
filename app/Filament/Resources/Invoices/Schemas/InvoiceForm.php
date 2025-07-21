<?php

namespace App\Filament\Resources\Invoices\Schemas;

use App\Models\Client;
use App\Models\UserCompany;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('user_id')
                    ->default(fn () => Auth::id()),

                TextInput::make('invoice_number')
                    ->label('Broj fakture')
                    ->disabled()
                    ->default(fn () => 'Auto-generiše se')
                    ->dehydrated(false),

                DatePicker::make('issue_date')
                    ->label('Datum fakture')
                    ->required()
                    ->default(now()),

                DatePicker::make('due_date')
                    ->label('Datum prometa')
                    ->required()
                    ->default(now()),

                TextInput::make('trading_place')
                    ->label('Mesto prometa')
                    ->default(function () {
                        $userCompany = UserCompany::where('user_id', Auth::id())->first();

                        return $userCompany?->company_city ?? 'Beograd';
                    })
                    ->required(),

                Select::make('client_id')
                    ->label('Klijent')
                    ->searchable()
                    ->getSearchResultsUsing(fn (string $search): array => Client::where('company_name', 'like', "%{$search}%")
                        ->orWhere('tax_id', 'like', "%{$search}%")
                        ->limit(50)
                        ->pluck('company_name', 'id')
                        ->toArray())
                    ->getOptionLabelUsing(fn ($value): ?string => Client::find($value)?->company_name)
                    ->createOptionForm([
                        TextInput::make('company_name')
                            ->label('Naziv kompanije')
                            ->required(),
                        TextInput::make('tax_id')
                            ->label('PIB')
                            ->required(),
                        TextInput::make('address')
                            ->label('Adresa')
                            ->required(),
                        TextInput::make('email')
                            ->label('Email')
                            ->email(),
                        TextInput::make('phone')
                            ->label('Telefon'),
                    ])
                    ->createOptionUsing(function (array $data): int {
                        $data['user_id'] = Auth::id();

                        return Client::create($data)->getKey();
                    })
                    ->required()
                    ->columnSpanFull(),

                Repeater::make('items')
                    ->label('Stavke fakture')
                    ->relationship()
                    ->schema([
                        TextInput::make('title')
                            ->label('Naziv usluge')
                            ->required(),

                        TextInput::make('unit')
                            ->label('Jed. mere')
                            ->default('kom')
                            ->required(),

                        TextInput::make('quantity')
                            ->label('Količina')
                            ->numeric()
                            ->default(1)
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, $get, $set) {
                                $quantity = (float) ($state ?: 0);
                                $unitPrice = (float) ($get('unit_price') ?: 0);
                                $set('amount', number_format($quantity * $unitPrice, 2, '.', ''));
                            }),

                        TextInput::make('unit_price')
                            ->label('Cena')
                            ->numeric()
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, $get, $set) {
                                $quantity = (float) ($get('quantity') ?: 0);
                                $unitPrice = (float) ($state ?: 0);
                                $set('amount', number_format($quantity * $unitPrice, 2, '.', ''));
                            }),

                        TextInput::make('amount')
                            ->label('Ukupno')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(),

                        Textarea::make('description')
                            ->label('Opis')
                            ->rows(1)
                            ->columnSpanFull(),
                    ])
                    ->columns(6)
                    ->defaultItems(1)
                    ->columnSpanFull(),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'unpaid' => 'Neplaćeno',
                        'paid' => 'Plaćeno',
                    ])
                    ->default('unpaid')
                    ->required(),

                TextInput::make('currency')
                    ->label('Valuta')
                    ->default('RSD')
                    ->required(),
            ])
            ->columns(2);
    }
}
