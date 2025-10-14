<?php

namespace App\Filament\Resources\Profakturas\Pages;

use App\Filament\Resources\Profakturas\ProfakturaResource;
use App\Models\BankAccount;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;

class CustomCreateProfaktura extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = ProfakturaResource::class;

    protected static ?string $navigationLabel = 'Nova Profaktura';

    protected static ?string $title = 'Nova Profaktura';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?int $navigationSort = 15;

    protected string $view = 'filament.resources.custom-form';

    public ?array $data = [];

    public string $invoice_type = 'domestic';
    
    public ?int $selectedClientId = null;

    public function mount(): void
    {
        $this->data = [
            'invoice_type' => 'domestic',
            'invoice_document_type' => 'profaktura',
            'invoice_number' => '',
            'issue_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'currency' => 'RSD',
            'status' => 'in_preparation',
            'client_id' => null,
            'description' => '',
            'trading_place' => 'Beograd',
            'invoice_items' => [
                [
                    'type' => 'service',
                    'description' => '',
                    'unit' => 'kom',
                    'quantity' => 1,
                    'unit_price' => 0.00,
                    'discount_value' => 0,
                    'discount_type' => 'percent',
                    'total' => 0.00,
                ]
            ],
        ];
        
        $this->form->fill($this->data);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Tip profakture')
                    ->description('Izaberite tip profakture na osnovu lokacije klijenta')
                    ->schema([
                        Radio::make('invoice_type')
                            ->label('Tip profakture')
                            ->options([
                                'domestic' => 'Domaća profaktura',
                                'foreign' => 'Inostrana profaktura',
                            ])
                            ->default('domestic')
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->invoice_type = $state;
                            }),
                    ]),

                Section::make('Osnovne informacije')
                    ->schema([
                        Select::make('client_id')
                            ->label('Klijent')
                            ->options(function () {
                                $isDomestic = $this->invoice_type === 'domestic';

                                return Client::where('user_id', Auth::id())
                                    ->where('is_domestic', $isDomestic)
                                    ->get()
                                    ->pluck('company_name', 'id');
                            })
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                $this->selectedClientId = $state;
                                $this->dispatch('client-updated');

                                // Auto-set currency from client's default
                                if ($state) {
                                    $client = Client::find($state);
                                    if ($client && $client->currency) {
                                        $set('currency', $client->currency);

                                        // Auto-select primary bank account for this currency
                                        $primaryAccount = BankAccount::whereHas('userCompany', function ($query) {
                                                $query->where('user_id', Auth::id());
                                            })
                                            ->where('currency', $client->currency)
                                            ->where('is_primary', true)
                                            ->first();

                                        if ($primaryAccount) {
                                            $set('bank_account_id', $primaryAccount->id);
                                        }
                                    }
                                }
                            })
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
                                TextInput::make('city')
                                    ->label('Grad')
                                    ->visible(fn () => $this->invoice_type === 'foreign'),
                                TextInput::make('country')
                                    ->label('Zemlja')
                                    ->visible(fn () => $this->invoice_type === 'foreign'),
                                TextInput::make('vat_number')
                                    ->label('VAT/EIB broj')
                                    ->visible(fn () => $this->invoice_type === 'foreign'),
                                TextInput::make('registration_number')
                                    ->label('ID/MB broj')
                                    ->visible(fn () => $this->invoice_type === 'foreign'),
                                Select::make('currency')
                                    ->label('Podrazumevana valuta')
                                    ->options([
                                        'RSD' => 'RSD - Srpski dinar',
                                        'EUR' => 'EUR - Evro',
                                        'USD' => 'USD - Američki dolar',
                                        'GBP' => 'GBP - Britanska funta',
                                        'CHF' => 'CHF - Švajcarski franak',
                                    ])
                                    ->default($this->invoice_type === 'foreign' ? 'EUR' : 'RSD')
                                    ->required(),
                                TextInput::make('email')
                                    ->label('Email')
                                    ->email(),
                                TextInput::make('phone')
                                    ->label('Telefon'),
                            ])
                            ->createOptionUsing(function (array $data, $set) {
                                $data['user_id'] = Auth::id();
                                $data['is_domestic'] = $this->invoice_type === 'domestic';

                                $client = Client::create($data);

                                // Auto-set invoice currency from newly created client
                                if ($client->currency) {
                                    $set('currency', $client->currency);
                                }

                                return $client->id;
                            }),

                        TextInput::make('invoice_number')
                            ->label('Broj profakture')
                            ->placeholder('Ostavite prazno za automatsko generisanje (P1/2025)')
                            ->helperText('Ako ostavite prazno, broj će biti automatski generisan sa prefiksom P'),

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
                            }),

                        Select::make('bank_account_id')
                            ->label('Bankovni račun')
                            ->placeholder('Izaberite bankovni račun')
                            ->options(function ($get) {
                                $currency = $get('currency') ?? 'RSD';

                                return BankAccount::whereHas('userCompany', function ($query) {
                                        $query->where('user_id', Auth::id());
                                    })
                                    ->where('currency', $currency)
                                    ->get()
                                    ->mapWithKeys(function ($account) {
                                        $label = $account->bank_name . ' - ' . $account->account_number;
                                        if ($account->is_primary) {
                                            $label .= ' (Podrazumevani)';
                                        }
                                        return [$account->id => $label];
                                    });
                            })
                            ->searchable()
                            ->helperText(fn ($get) => 'Prikazani su samo računi u valuti: ' . ($get('currency') ?? 'RSD'))
                            ->live()
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Opis')
                            ->nullable()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Informacije o učesnicima')
                    ->description('Podaci o vašoj kompaniji i klijentu')
                    ->schema([
                        Placeholder::make('company_info')
                            ->label('Izdavalac (Vaša kompanija)')
                            ->content(function ($get) {
                                $user = Auth::user();
                                
                                $info = [];
                                $info[] = $user->company_name ?? 'SR Software Niš';
                                $info[] = 'STEFAN RAKIĆ PR RAČUNARSKO PROGRAMIRANJE SR SOFTWARE NIŠ';
                                $info[] = $user->address ?? 'Vojvode Tankosica 11/63';
                                $info[] = 'Niš 18000';
                                $info[] = 'E-mail: ' . ($user->email ?? 'stefanrakic92@gmail.com');
                                $info[] = 'PIB: 109270190';
                                $info[] = 'MB: 64056891';
                                
                                // Show SWIFT and IBAN for foreign invoices
                                if (($get('invoice_type') ?? 'domestic') === 'foreign') {
                                    if ($user->swift_code) {
                                        $info[] = 'SWIFT: ' . $user->swift_code;
                                    }
                                    if ($user->iban) {
                                        $info[] = 'IBAN: ' . $user->iban;
                                    }
                                }
                                
                                return new \Illuminate\Support\HtmlString('<div class="space-y-1">' . 
                                    implode('<br>', array_map(fn($line) => '<div>' . e($line) . '</div>', $info)) . 
                                '</div>');
                            })
                            ->columnSpan(1),

                        Placeholder::make('client_info')
                            ->label('Kupac (Klijent)')
                            ->content(function () {
                                $clientId = $this->selectedClientId;
                                
                                if (!$clientId) {
                                    return 'Izaberite klijenta da vidite informacije';
                                }

                                $client = Client::find($clientId);
                                if (!$client) {
                                    return 'Klijent nije pronađen';
                                }

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

                Section::make('Stavke profakture')
                    ->description('Dodajte stavke sa cenama')
                    ->schema([
                        Repeater::make('invoice_items')
                            ->label('Stavke')
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

                                TextInput::make('description')
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
                                    ]),

                                TextInput::make('quantity')
                                    ->label('Količina')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        $this->calculateItemTotal($set, $get);
                                    })
                                    ->columnSpan(1),

                                TextInput::make('unit_price')
                                    ->label('Cena')
                                    ->numeric()
                                    ->step(0.01)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        $this->calculateItemTotal($set, $get);
                                    })
                                    ->columnSpan(2),

                                TextInput::make('discount_value')
                                    ->label('Popust')
                                    ->numeric()
                                    ->step(0.01)
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        $this->calculateItemTotal($set, $get);
                                    })
                                    ->columnSpan(1),

                                Select::make('discount_type')
                                    ->label('Tip pop.')
                                    ->options(function ($get) {
                                        $currency = $get('../../currency') ?? 'RSD';
                                        return [
                                            'percent' => '%',
                                            'fixed' => $currency,
                                        ];
                                    })
                                    ->default('percent')
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        $this->calculateItemTotal($set, $get);
                                    })
                                    ->columnSpan(1),

                                TextInput::make('total')
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
                            ->defaultItems(1)
                            ->collapsible()
                            ->collapsed(false)
                            ->live(),

                        Placeholder::make('invoice_total')
                            ->label('UKUPNO ZA PROFAKTURU')
                            ->content(function ($get) {
                                $total = 0;
                                
                                $invoiceItems = $get('invoice_items') ?? [];
                                if (is_array($invoiceItems)) {
                                    foreach ($invoiceItems as $item) {
                                        if (isset($item['total']) && is_numeric($item['total'])) {
                                            $total += (float) $item['total'];
                                        }
                                    }
                                }

                                $currency = $get('currency') ?? 'RSD';

                                return new \Illuminate\Support\HtmlString('<div class="text-2xl font-bold text-blue-600">'.number_format($total, 2).' '.$currency.'</div>');
                            })
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function createInvoice(string $status): void
    {
        // Validate form data
        $this->form->validate();
        
        $data = $this->data;
        
        // Check if client_id is present
        if (!isset($data['client_id']) || empty($data['client_id'])) {
            Notification::make()
                ->title('Greška')
                ->body('Morate izabrati klijenta')
                ->danger()
                ->send();
            return;
        }
        
        $invoiceData = [
            'user_id' => Auth::id(),
            'client_id' => $data['client_id'],
            'bank_account_id' => $data['bank_account_id'] ?? null,
            'invoice_type' => $data['invoice_type'],
            'invoice_document_type' => 'profaktura',
            'issue_date' => $data['issue_date'],
            'due_date' => $data['due_date'],
            'trading_place' => $data['trading_place'],
            'currency' => $data['currency'],
            'description' => $data['description'],
            'status' => $status,
            'amount' => 0, // Will be updated automatically after items are created
        ];

        // Add custom invoice number if provided, otherwise auto-generate
        if (!empty($data['invoice_number'])) {
            $invoiceData['invoice_number'] = $data['invoice_number'];
        }
        // Note: If invoice_number is empty, it will be auto-generated by the model's boot method

        $invoice = Invoice::create($invoiceData);

        // Create invoice items
        if (isset($data['invoice_items'])) {
            foreach ($data['invoice_items'] as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'title' => $item['description'], // Map to existing 'title' field
                    'description' => $item['description'],
                    'type' => $item['type'],
                    'unit' => $item['unit'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_value' => $item['discount_value'] ?? 0,
                    'discount_type' => $item['discount_type'] ?? 'percent',
                    'amount' => $item['total'],
                ]);
            }
            
            // Update invoice amount after all items are created
            $invoice->updateAmount();
        }

        // Show different notifications based on action
        $messages = [
            'in_preparation' => [
                'title' => 'Profaktura je sačuvana',
                'body' => "Profaktura broj {$invoice->invoice_number} je sačuvana kao nacrt. Možete je kasnije izdati."
            ],
            'issued' => [
                'title' => 'Profaktura je izdata',
                'body' => "Profaktura broj {$invoice->invoice_number} je uspešno izdata i spremna za slanje."
            ],
            'sent' => [
                'title' => 'Profaktura je izdata i poslana',
                'body' => "Profaktura broj {$invoice->invoice_number} je uspešno izdata i označena kao poslana."
            ]
        ];

        $message = $messages[$status] ?? $messages['in_preparation'];

        Notification::make()
            ->title($message['title'])
            ->body($message['body'])
            ->success()
            ->send();

        $this->redirect(ProfakturaResource::getUrl('edit', ['record' => $invoice->id]));
    }
    
    // Keep the old create method for backward compatibility
    public function create(): void
    {
        $this->createInvoice('in_preparation');
    }

    public function calculateItemTotal($set, $get): void
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
        $set('total', max(0, round($total, 2)));
    }

    protected function updateDiscountTypeOptions(string $currency): void
    {
        // This method is called when currency changes
        // The discount_type options will be automatically updated via the reactive options() function
        
        // Force form to refresh discount type fields
        $this->form->fill($this->data);
    }

    protected function recalculateAllTotals(): void
    {
        // Recalculate totals for all invoice items when currency changes
        if (isset($this->data['invoice_items']) && is_array($this->data['invoice_items'])) {
            foreach ($this->data['invoice_items'] as $index => $item) {
                $quantity = (float) ($item['quantity'] ?? 0);
                $unitPrice = (float) ($item['unit_price'] ?? 0);
                $discountValue = (float) ($item['discount_value'] ?? 0);
                $discountType = $item['discount_type'] ?? 'percent';

                $subtotal = $quantity * $unitPrice;

                if ($discountType === 'percent') {
                    $discount = $subtotal * ($discountValue / 100);
                } else {
                    $discount = $discountValue;
                }

                $total = $subtotal - $discount;
                $this->data['invoice_items'][$index]['total'] = max(0, round($total, 2));
            }
        }
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Sačuvaj')
                ->icon('heroicon-o-document')
                ->color('gray')
                ->action('saveAsDraft'),
            
            Action::make('issue')
                ->label('Izdaj profakturu')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->action('issueInvoice'),
                
            Action::make('send')
                ->label('Izdaj i pošalji')
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->action('issueAndSend')
                ->keyBindings(['mod+s']),
        ];
    }

    public function saveAsDraft(): void
    {
        $this->createInvoice('in_preparation');
    }

    public function issueInvoice(): void
    {
        $this->createInvoice('issued');
    }

    public function issueAndSend(): void
    {
        $this->createInvoice('sent');
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}