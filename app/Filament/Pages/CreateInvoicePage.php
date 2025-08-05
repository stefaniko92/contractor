<?php

namespace App\Filament\Pages;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\Auth;

class CreateInvoicePage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-plus';

    protected string $view = 'filament.pages.create-invoice-page';

    protected static string|\UnitEnum|null $navigationGroup = 'Fakturisanje';

    protected static ?string $navigationLabel = 'Nova Faktura';

    protected static ?string $title = 'Nova Faktura';

    protected static ?int $navigationSort = 15;

    public ?array $data = [];

    public string $invoice_type = 'domestic';
    
    public ?int $client_id = null;

    public function mount(): void
    {
        $this->data = [
            'invoice_type' => 'domestic',
            'client_id' => null,
            'invoice_number' => '',
            'issue_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'trading_place' => 'Beograd',
            'currency' => 'RSD',
            'description' => null,
            'status' => 'unpaid',
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
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make('Tip fakture')
                ->description('Izaberite tip fakture na osnovu lokacije klijenta')
                ->schema([
                    Radio::make('invoice_type')
                        ->label('Tip fakture')
                        ->options([
                            'domestic' => 'Domaća faktura',
                            'foreign' => 'Inostrana faktura',
                        ])
                        ->default('domestic')
                        ->live()
                        ->afterStateUpdated(function ($state, $set) {
                            $this->invoice_type = $state;
                            $set('invoice_type', $state);
                        }),
                ]),

            Section::make('Izbor klijenta')
                ->description('Izaberite postojeći klijent ili kreirajte novi')
                ->schema([
                    Select::make('client_id')
                        ->label('Klijenti')
                        ->placeholder('Pretraži klijente...')
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
                            $this->client_id = $state;
                            $set('client_id', $state);
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
                            TextInput::make('email')
                                ->label('Email')
                                ->email(),
                            TextInput::make('phone')
                                ->label('Telefon'),
                        ])
                        ->createOptionUsing(function (array $data) {
                            $data['user_id'] = Auth::id();
                            $data['is_domestic'] = $this->invoice_type === 'domestic';

                            return Client::create($data)->id;
                        })
                        ->columnSpanFull(),
                ]),

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
                            
                            // Show SWIFT and IBAN for foreign invoices
                            if (($this->data['invoice_type'] ?? 'domestic') === 'foreign') {
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
                            $clientId = $this->data['client_id'] ?? null;
                            
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

            Section::make('Osnovne informacije fakture')
                ->schema([
                    TextInput::make('invoice_number')
                        ->label('Broj fakture')
                        ->placeholder('Ostavite prazno za automatsko generisanje')
                        ->helperText('Ako ne unesete broj, automatski će se generisati'),

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
                        ->afterStateUpdated(function ($state, $set, $get) {
                            // Update discount type options based on currency
                            $this->updateDiscountTypeOptions($state);
                            // Recalculate all item totals
                            $this->recalculateAllTotals();
                        }),

                    Textarea::make('description')
                        ->label('Opis')
                        ->nullable()
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('Stavke fakture')
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
                                ->options(function () {
                                    $currency = $this->data['currency'] ?? 'RSD';
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
                        ->label('UKUPNO ZA FAKTURU')
                        ->content(function () {
                            $total = 0;
                            
                            if (isset($this->data['invoice_items']) && is_array($this->data['invoice_items'])) {
                                foreach ($this->data['invoice_items'] as $item) {
                                    if (isset($item['total']) && is_numeric($item['total'])) {
                                        $total += (float) $item['total'];
                                    }
                                }
                            }

                            $currency = $this->data['currency'] ?? 'RSD';

                            return new \Illuminate\Support\HtmlString('<div class="text-2xl font-bold text-blue-600">'.number_format($total, 2).' '.$currency.'</div>');
                        })
                        ->columnSpanFull(),
                ]),
        ];
    }

    protected function getFormStatePath(): string
    {
        return 'data';
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

    public function create(): void
    {
        // Validate form data
        $this->form->validate();
        
        $data = $this->data;
        
        // Debug: Log the form data
        \Log::info('Form data:', $data);
        
        // Check if client_id is present
        if (!isset($data['client_id']) || empty($data['client_id'])) {
            Notification::make()
                ->title('Greška')
                ->body('Morate izabrati klijenta')
                ->danger()
                ->send();
            return;
        }
        
        // Calculate total amount from items
        $totalAmount = 0;
        if (isset($data['invoice_items'])) {
            $totalAmount = collect($data['invoice_items'])->sum('total');
        }

        $invoiceData = [
            'user_id' => Auth::id(),
            'client_id' => $data['client_id'],
            'invoice_type' => $data['invoice_type'],
            'issue_date' => $data['issue_date'],
            'due_date' => $data['due_date'],
            'trading_place' => $data['trading_place'],
            'currency' => $data['currency'],
            'description' => $data['description'],
            'status' => 'unpaid',
            'amount' => $totalAmount,
        ];

        // Add custom invoice number if provided
        if (! empty($data['invoice_number'])) {
            $invoiceData['invoice_number'] = $data['invoice_number'];
        }

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
        }

        Notification::make()
            ->title('Faktura je uspešno kreirana')
            ->body("Kreirana faktura broj: {$invoice->invoice_number}")
            ->success()
            ->send();

        $this->redirect('/admin/invoices/'.$invoice->id.'/edit');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('create')
                ->label('Kreiraj fakturu')
                ->submit('create')
                ->keyBindings(['mod+s'])
                ->color('primary')
            ->extraAttributes(['style' => 'margin-top: 20px']),
        ];
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
}
