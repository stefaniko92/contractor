<?php

namespace App\Filament\Resources\Invoices\Schemas;

use App\Models\BankAccount;
use App\Models\Client;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Hidden::make('user_id')
                            ->default(fn () => Auth::id()),

                        Section::make('Klijent')
                            ->schema([
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
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        // When client changes, update currency based on client type
                                        if ($state) {
                                            $client = Client::find($state);
                                            if ($client) {
                                                $isDomestic = $client->is_domestic === 1 || $client->is_domestic === true || $client->is_domestic === '1';

                                                // Set currency based on client type
                                                $newCurrency = $isDomestic ? 'RSD' : 'EUR';
                                                $set('currency', $newCurrency);

                                                // Auto-select primary bank account for the new currency
                                                $primaryAccount = BankAccount::whereHas('userCompany', function ($query) {
                                                    $query->where('user_id', Auth::id());
                                                })
                                                    ->where('currency', $newCurrency)
                                                    ->where('is_primary', true)
                                                    ->first();

                                                if ($primaryAccount) {
                                                    $set('bank_account_id', $primaryAccount->id);
                                                } else {
                                                    // Clear bank account if no matching currency
                                                    $set('bank_account_id', null);
                                                }
                                            }
                                        }
                                    })
                                    ->columnSpanFull(),
                            ])
                            ->columnSpanFull(),

                        Section::make('Osnovne informacije fakture')
                            ->schema([
                                Hidden::make('invoice_document_type')
                                    ->default('faktura'),

                                TextInput::make('invoice_number')
                                    ->label('Broj fakture')
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('trading_place')
                                    ->label('Mesto prometa')
                                    ->default('Beograd'),

                                DatePicker::make('issue_date')
                                    ->label('Datum izdavanja')
                                    ->required()
                                    ->default(now()),

                                DatePicker::make('due_date')
                                    ->label('Datum dospeća')
                                    ->required()
                                    ->default(now()->addDays(30)),

                                Select::make('currency')
                                    ->label('Valuta')
                                    ->options([
                                        'RSD' => 'RSD - Srpski dinar',
                                        'EUR' => 'EUR - Evro',
                                        'USD' => 'USD - Američki dolar',
                                        'CHF' => 'CHF - Švajcarski franak',
                                        'GBP' => 'GBP - Britanska funta',
                                    ])
                                    ->default('RSD')
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set) {
                                        // Auto-select primary bank account for new currency
                                        $primaryAccount = BankAccount::whereHas('userCompany', function ($query) {
                                            $query->where('user_id', Auth::id());
                                        })
                                            ->where('currency', $state)
                                            ->where('is_primary', true)
                                            ->first();

                                        if ($primaryAccount) {
                                            $set('bank_account_id', $primaryAccount->id);
                                        } else {
                                            // Clear bank account if no matching currency
                                            $set('bank_account_id', null);
                                        }
                                    })
                                    ->helperText(function ($get) {
                                        $clientId = $get('client_id');
                                        if ($clientId) {
                                            $client = Client::find($clientId);
                                            if ($client) {
                                                $isDomestic = $client->is_domestic === 1 || $client->is_domestic === true || $client->is_domestic === '1';
                                                if (! $isDomestic) {
                                                    return 'Za inostrane klijente koristite EUR, USD, CHF ili GBP';
                                                }
                                            }
                                        }

                                        return null;
                                    }),

                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'sent' => 'Poslana',
                                        'issued' => 'Izdata',
                                        'in_preparation' => 'U pripremi',
                                        'charged' => 'Naplaćena',
                                        'uncharged' => 'Nenaplaćena',
                                        'storned' => 'Stornirana',
                                    ])
                                    ->default('in_preparation')
                                    ->required(),

                                Select::make('bank_account_id')
                                    ->label('Bankovni račun')
                                    ->placeholder('Izaberite bankovni račun')
                                    ->options(function ($get, $record) {
                                        $currency = $get('currency') ?? $record?->currency ?? 'RSD';

                                        return BankAccount::whereHas('userCompany', function ($query) {
                                            $query->where('user_id', Auth::id());
                                        })
                                            ->where('currency', $currency)
                                            ->get()
                                            ->mapWithKeys(function ($account) {
                                                // Use IBAN for foreign accounts, account_number for domestic
                                                $accountNumber = $account->account_type === 'foreign'
                                                    ? ($account->iban ?? 'N/A')
                                                    : ($account->account_number ?? 'N/A');

                                                $label = $account->bank_name.' - '.$accountNumber;
                                                if ($account->is_primary) {
                                                    $label .= ' (Podrazumevani)';
                                                }

                                                return [$account->id => $label];
                                            });
                                    })
                                    ->searchable()
                                    ->helperText(function ($get, $record) {
                                        $currency = $get('currency') ?? $record?->currency ?? 'RSD';
                                        $count = BankAccount::whereHas('userCompany', function ($query) {
                                            $query->where('user_id', Auth::id());
                                        })
                                            ->where('currency', $currency)
                                            ->count();

                                        if ($count === 0) {
                                            return "Nemate bankovnih računa u valuti {$currency}. Dodajte račun u sekciji Bankovni računi.";
                                        }

                                        return "Prikazani su samo računi u valuti: {$currency}";
                                    })
                                    ->live()
                                    ->default(function ($record) {
                                        // If editing and no bank account set, auto-select primary for this currency
                                        if ($record && ! $record->bank_account_id && $record->currency) {
                                            $primaryAccount = BankAccount::whereHas('userCompany', function ($query) {
                                                $query->where('user_id', Auth::id());
                                            })
                                                ->where('currency', $record->currency)
                                                ->where('is_primary', true)
                                                ->first();

                                            return $primaryAccount?->id;
                                        }

                                        return null;
                                    })
                                    ->columnSpanFull(),

                                Textarea::make('description')
                                    ->label('Opis')
                                    ->nullable()
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),

                        Section::make('Stavke fakture')
                            ->description('Stavke sa cenama')
                            ->schema([
                                Repeater::make('items')
                                    ->label('Stavke')
                                    ->relationship()
                                    ->schema([
                                        Select::make('type')
                                            ->label('Tip')
                                            ->options([
                                                'service' => 'Usluga',
                                                'product' => 'Proizvod',
                                            ])
                                            ->default('service')
                                            ->columnSpan(2)
                                            ->required(),

                                        TextInput::make('title')
                                            ->label('Naziv')
                                            ->required()
                                            ->columnSpan(3),

                                        Select::make('unit')
                                            ->label('Jedinica')
                                            ->options([
                                                'kom' => 'komad',
                                                'sat' => 'sat',
                                                'm' => 'm',
                                                'm2' => 'm2',
                                                'm3' => 'm3',
                                                'kg' => 'kg',
                                                'l' => 'l',
                                                'pak' => 'pak',
                                                'reč' => 'reč',
                                                'dan' => 'dan',
                                            ])
                                            ->columnSpan(1),

                                        TextInput::make('quantity')
                                            ->label('Količina')
                                            ->numeric()
                                            ->default(1)
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function ($state, $get, $set) {
                                                self::calculateItemTotal($get, $set);
                                            })
                                            ->columnSpan(1),

                                        TextInput::make('unit_price')
                                            ->label('Cena')
                                            ->numeric()
                                            ->step(0.01)
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function ($state, $get, $set) {
                                                self::calculateItemTotal($get, $set);
                                            })
                                            ->columnSpan(2),

                                        TextInput::make('discount_value')
                                            ->label('Popust')
                                            ->numeric()
                                            ->step(0.01)
                                            ->default(0)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function ($state, $get, $set) {
                                                self::calculateItemTotal($get, $set);
                                            })
                                            ->columnSpan(1),

                                        Select::make('discount_type')
                                            ->label('Tip pop.')
                                            ->options([
                                                'percent' => '%',
                                                'fixed' => 'RSD',
                                            ])
                                            ->default('percent')
                                            ->live()
                                            ->afterStateUpdated(function ($state, $get, $set) {
                                                self::calculateItemTotal($get, $set);
                                            })
                                            ->columnSpan(1),

                                        TextInput::make('amount')
                                            ->label('Ukupno')
                                            ->numeric()
                                            ->step(0.01)
                                            ->disabled()
                                            ->dehydrated()
                                            ->default(0)
                                            ->columnSpan(1),
                                    ])
                                    ->columns(12)
                                    ->columnSpanFull()
                                    ->defaultItems(1),

                                Placeholder::make('invoice_total')
                                    ->label('UKUPNO ZA FAKTURU')
                                    ->content(function ($record, $get) {
                                        $total = 0;
                                        $items = $get('items') ?? [];

                                        foreach ($items as $item) {
                                            if (isset($item['amount'])) {
                                                $total += (float) $item['amount'];
                                            }
                                        }

                                        $currency = $get('currency') ?? 'RSD';

                                        return new \Illuminate\Support\HtmlString('<div class="text-2xl font-bold text-blue-600">'.number_format($total, 2).' '.$currency.'</div>');
                                    })
                                    ->live()
                                    ->columnSpanFull(),
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    protected static function calculateItemTotal($get, $set): void
    {
        $quantity = (float) ($get('quantity') ?? 0);
        $unitPrice = (float) ($get('unit_price') ?? 0);
        $discountValue = (float) ($get('discount_value') ?? 0);
        $discountType = $get('discount_type') ?? 'percent';

        $subtotal = $quantity * $unitPrice;

        if ($discountType === 'percent') {
            $discount = $subtotal * ($discountValue / 100);
        } else {
            $discount = $discountValue;
        }

        $total = $subtotal - $discount;
        $set('amount', max(0, round($total, 2)));
    }
}
