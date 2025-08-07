<?php

namespace App\Filament\Resources\Invoices\Schemas;

use App\Models\Client;
use App\Models\UserCompany;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
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
                Hidden::make('user_id')
                    ->default(fn () => Auth::id()),

                Section::make('Informacije o učesnicima')
                    ->description('Podaci o vašoj kompaniji i klijentu')
                    ->schema([
                        Placeholder::make('company_info')
                            ->label('Izdavalac (Vaša kompanija)')
                            ->content(function () {
                                $user = Auth::user();
                                
                                $info = [];
                                $info[] = $user->company_name ?? 'SR Software Niš';
                                $info[] = 'STEFAN RAKIĆ PR RAČUNARSKO PROGRAMIRANJE SR SOFTWARE NIŠ';
                                $info[] = $user->address ?? 'Vojvode Tankosica 11/63';
                                $info[] = 'Niš 18000';
                                $info[] = 'E-mail: ' . ($user->email ?? 'stefanrakic92@gmail.com');
                                $info[] = 'PIB: 109270190';
                                $info[] = 'MB: 64056891';
                                
                                return new \Illuminate\Support\HtmlString('<div class="space-y-1">' . 
                                    implode('<br>', array_map(fn($line) => '<div>' . e($line) . '</div>', $info)) . 
                                '</div>');
                            })
                            ->columnSpan(1),

                        Placeholder::make('client_info')
                            ->label('Kupac (Klijent)')
                            ->content(function ($record) {
                                if (!$record || !$record->client) {
                                    return 'Klijent nije pronađen';
                                }

                                $client = $record->client;
                                $info = [];
                                $info[] = $client->company_name;
                                $info[] = $client->address;
                                
                                if ($client->city) {
                                    $city = $client->city;
                                    if ($client->country) {
                                        $city .= ', ' . $client->country;
                                    }
                                    $info[] = $city;
                                }
                                
                                if ($client->email) {
                                    $info[] = 'E-mail: ' . $client->email;
                                }
                                
                                if ($client->phone) {
                                    $info[] = 'Telefon: ' . $client->phone;
                                }
                                
                                if ($client->tax_id) {
                                    $info[] = 'PIB: ' . $client->tax_id;
                                }
                                
                                if ($client->registration_number) {
                                    $info[] = 'MB: ' . $client->registration_number;
                                }
                                
                                if ($client->vat_number) {
                                    $info[] = 'VAT/EIB: ' . $client->vat_number;
                                }

                                return new \Illuminate\Support\HtmlString('<div class="space-y-1">' . 
                                    implode('<br>', array_map(fn($line) => '<div>' . e($line) . '</div>', $info)) . 
                                '</div>');
                            })
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Section::make('Osnovne informacije fakture')
                    ->schema([
                        Hidden::make('invoice_document_type')
                            ->default('faktura'),

                        TextInput::make('invoice_number')
                            ->label('Broj fakture')
                            ->disabled()
                            ->dehydrated(false),

                        DatePicker::make('issue_date')
                            ->label('Datum izdavanja')
                            ->required()
                            ->default(now()),

                        DatePicker::make('due_date')
                            ->label('Datum dospeća')
                            ->required()
                            ->default(now()->addDays(30)),

                        TextInput::make('trading_place')
                            ->label('Mesto prometa')
                            ->default('Beograd'),

                        Select::make('currency')
                            ->label('Valuta')
                            ->options([
                                'RSD' => 'RSD - Srpski dinar',
                                'EUR' => 'EUR - Evro',
                                'USD' => 'USD - Američki dolar',
                            ])
                            ->default('RSD')
                            ->required()
                            ->live(),

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

                        Textarea::make('description')
                            ->label('Opis')
                            ->nullable()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

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
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

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
                                        'dan' => 'dan'
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