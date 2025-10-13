<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Filament\Resources\Invoices\InvoiceResource;
use App\Models\Client;
use App\Models\Invoice;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
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

class CustomCreateInvoice extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = InvoiceResource::class;

    protected static ?string $navigationLabel = 'Nova Faktura';

    protected static ?string $title = 'Nova Faktura';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?int $navigationSort = 15;

    public ?array $data = [];

    public string $invoice_type = 'domestic';

    public function mount(): void
    {
        $this->data = [
            'invoice_type' => 'domestic',
            'invoice_document_type' => 'faktura',
            'invoice_number' => '',
            'client_id' => null,
            'issue_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'trading_place' => 'Beograd',
            'currency' => 'RSD',
            'description' => '',
            'status' => 'in_preparation',
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

        // Handle copying from existing invoice
        if (request()->has('copy_from_invoice')) {
            $invoiceId = request()->get('copy_from_invoice');
            $sourceInvoice = Invoice::with('items', 'client')->find($invoiceId);
            
            if ($sourceInvoice) {
                $this->data = [
                    'invoice_type' => $sourceInvoice->invoice_type,
                    'invoice_document_type' => $sourceInvoice->invoice_document_type,
                    'client_id' => $sourceInvoice->client_id,
                    'issue_date' => now()->format('Y-m-d'),
                    'due_date' => now()->addDays(30)->format('Y-m-d'),
                    'trading_place' => $sourceInvoice->trading_place,
                    'currency' => $sourceInvoice->currency,
                    'description' => $sourceInvoice->description,
                    'status' => 'in_preparation',
                    'invoice_items' => $sourceInvoice->items->map(function ($item) {
                        return [
                            'type' => $item->type,
                            'description' => $item->description,
                            'unit' => $item->unit,
                            'quantity' => $item->quantity,
                            'unit_price' => $item->unit_price,
                            'discount_value' => $item->discount_value,
                            'discount_type' => $item->discount_type,
                            'total' => $item->amount,
                        ];
                    })->toArray(),
                ];
                
                $this->invoice_type = $sourceInvoice->invoice_type;
                
                $documentTypeLabel = match($sourceInvoice->invoice_document_type) {
                    'faktura' => 'fakture',
                    'profaktura' => 'profakture',
                    'avansna_faktura' => 'avansne fakture',
                    default => 'dokumenta'
                };
                
                Notification::make()
                    ->title('Podaci kopirani')
                    ->body("Podaci su uspešno kopirani iz {$documentTypeLabel} {$sourceInvoice->invoice_number}. Možete da ih modifikujete pre kreiranja.")
                    ->success()
                    ->send();
            }
        }

        // Handle copying from profaktura  
        if (request()->has('copy_from_profaktura')) {
            $profakturaId = request()->get('copy_from_profaktura');
            $profaktura = Invoice::with('items', 'client')->find($profakturaId);
            
            if ($profaktura && $profaktura->invoice_document_type === 'profaktura') {
                $this->data = [
                    'invoice_type' => $profaktura->invoice_type,
                    'invoice_document_type' => 'faktura', // Convert to regular invoice
                    'client_id' => $profaktura->client_id,
                    'issue_date' => now()->format('Y-m-d'),
                    'due_date' => now()->addDays(30)->format('Y-m-d'),
                    'trading_place' => $profaktura->trading_place,
                    'currency' => $profaktura->currency,
                    'description' => 'Faktura na osnovu profakture ' . $profaktura->invoice_number . ' od ' . $profaktura->issue_date->format('d.m.Y'),
                    'status' => 'issued',
                    'invoice_items' => $profaktura->items->map(function ($item) {
                        return [
                            'type' => $item->type,
                            'description' => $item->description,
                            'unit' => $item->unit,
                            'quantity' => $item->quantity,
                            'unit_price' => $item->unit_price,
                            'discount_value' => $item->discount_value,
                            'discount_type' => $item->discount_type,
                            'total' => $item->amount,
                        ];
                    })->toArray(),
                ];
                
                $this->invoice_type = $profaktura->invoice_type;
                
                Notification::make()
                    ->title('Podaci kopirani')
                    ->body("Podaci su uspešno kopirani iz profakture {$profaktura->invoice_number}. Možete da ih modifikujete pre kreiranja fakture.")
                    ->success()
                    ->send();
            }
        }
        
        $this->form->fill($this->data);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Tip fakture')
                    ->description('Izaberite tip fakture na osnovu lokacije klijenta i vrstu dokumenta')
                    ->schema([
                        Radio::make('invoice_document_type')
                            ->label('Tip dokumenta')
                            ->options([
                                'faktura' => 'Faktura',
                                'profaktura' => 'Profaktura',
                                'avansna_faktura' => 'Avansna Faktura',
                            ])
                            ->default('faktura')
                            ->required()
                            ->inline()
                            ->columnSpanFull(),

                        Radio::make('invoice_type')
                            ->label('Tip fakture')
                            ->options([
                                'domestic' => 'Domaća faktura',
                                'foreign' => 'Inostrana faktura',
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
                                // Auto-set currency from client's default
                                if ($state) {
                                    $client = Client::find($state);
                                    if ($client && $client->currency) {
                                        $set('currency', $client->currency);
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
                            ->label('Broj fakture')
                            ->disabled()
                            ->placeholder('Automatski generiše'),

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
                            ->required(),

                        Textarea::make('description')
                            ->label('Opis')
                            ->nullable()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        $data = $this->form->getState();

        $invoice = Invoice::create([
            'user_id' => Auth::id(),
            'client_id' => $data['client_id'],
            'invoice_type' => $data['invoice_type'],
            'invoice_document_type' => $data['invoice_document_type'],
            'issue_date' => $data['issue_date'],
            'due_date' => $data['due_date'],
            'trading_place' => $data['trading_place'],
            'currency' => $data['currency'],
            'description' => $data['description'],
            'status' => 'in_preparation',
            'amount' => 0,
        ]);

        Notification::make()
            ->title('Faktura je uspešno kreirana')
            ->success()
            ->send();

        $this->redirect(InvoiceResource::getUrl('edit', ['record' => $invoice->id]));
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('create')
                ->label('Kreiraj fakturu')
                ->submit('create')
                ->keyBindings(['mod+s']),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
