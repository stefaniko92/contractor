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

    public static function getNavigationLabel(): string
    {
        return __('create_invoice.navigation_label');
    }

    public function getTitle(): string
    {
        return __('create_invoice.title');
    }

    protected static bool $shouldRegisterNavigation = false;

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
                    'client_id' => $sourceInvoice->client_id,
                    'invoice_number' => '',
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
                $this->client_id = $sourceInvoice->client_id;
                
                $documentTypeLabel = match($sourceInvoice->invoice_document_type) {
                    'faktura' => __('create_invoice.document_types.faktura'),
                    'profaktura' => __('create_invoice.document_types.profaktura'),
                    'avansna_faktura' => __('create_invoice.document_types.avansna_faktura'),
                    default => __('create_invoice.document_types.default')
                };

                Notification::make()
                    ->title(__('create_invoice.notifications.copied_from_invoice.title'))
                    ->body(__('create_invoice.notifications.copied_from_invoice.body', [
                        'type' => $documentTypeLabel,
                        'number' => $sourceInvoice->invoice_number
                    ]))
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
                    'client_id' => $profaktura->client_id,
                    'invoice_number' => '',
                    'issue_date' => now()->format('Y-m-d'),
                    'due_date' => now()->addDays(30)->format('Y-m-d'),
                    'trading_place' => $profaktura->trading_place,
                    'currency' => $profaktura->currency,
                    'description' => 'Faktura na osnovu profakture ' . $profaktura->invoice_number . ' od ' . $profaktura->issue_date->format('d.m.Y'),
                    'status' => 'in_preparation',
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
                $this->client_id = $profaktura->client_id;
                
                Notification::make()
                    ->title(__('create_invoice.notifications.copied_from_profaktura.title'))
                    ->body(__('create_invoice.notifications.copied_from_profaktura.body', [
                        'number' => $profaktura->invoice_number
                    ]))
                    ->success()
                    ->send();
            }
        }
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make(__('create_invoice.sections.invoice_type.title'))
                ->description(__('create_invoice.sections.invoice_type.description'))
                ->schema([
                    Radio::make('invoice_type')
                        ->label(__('create_invoice.fields.invoice_type.label'))
                        ->options([
                            'domestic' => __('create_invoice.fields.invoice_type.options.domestic'),
                            'foreign' => __('create_invoice.fields.invoice_type.options.foreign'),
                        ])
                        ->default('domestic')
                        ->live()
                        ->afterStateUpdated(function ($state, $set) {
                            $this->invoice_type = $state;
                            $set('invoice_type', $state);
                        }),
                ]),

            Section::make(__('create_invoice.sections.client_selection.title'))
                ->description(__('create_invoice.sections.client_selection.description'))
                ->schema([
                    Select::make('client_id')
                        ->label(__('create_invoice.fields.client_id.label'))
                        ->placeholder(__('create_invoice.fields.client_id.placeholder'))
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
                                ->label(__('create_invoice.client_form.company_name'))
                                ->required(),
                            TextInput::make('tax_id')
                                ->label(__('create_invoice.client_form.tax_id'))
                                ->required(),
                            TextInput::make('address')
                                ->label(__('create_invoice.client_form.address'))
                                ->required(),
                            TextInput::make('city')
                                ->label(__('create_invoice.client_form.city'))
                                ->visible(fn () => $this->invoice_type === 'foreign'),
                            TextInput::make('country')
                                ->label(__('create_invoice.client_form.country'))
                                ->visible(fn () => $this->invoice_type === 'foreign'),
                            TextInput::make('vat_number')
                                ->label(__('create_invoice.client_form.vat_number'))
                                ->visible(fn () => $this->invoice_type === 'foreign'),
                            TextInput::make('registration_number')
                                ->label(__('create_invoice.client_form.registration_number'))
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
                                ->label(__('create_invoice.client_form.email'))
                                ->email(),
                            TextInput::make('phone')
                                ->label(__('create_invoice.client_form.phone')),
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
                        })
                        ->columnSpanFull(),
                ]),

            Section::make(__('create_invoice.sections.participants_info.title'))
                ->description(__('create_invoice.sections.participants_info.description'))
                ->schema([
                    Placeholder::make('company_info')
                        ->label(__('create_invoice.fields.company_info.label'))
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
                        ->label(__('create_invoice.fields.client_info.label'))
                        ->content(function () {
                            $clientId = $this->data['client_id'] ?? null;

                            if (!$clientId) {
                                return __('create_invoice.fields.client_info.select_client');
                            }

                            $client = Client::find($clientId);
                            if (!$client) {
                                return __('create_invoice.fields.client_info.not_found');
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

            Section::make(__('create_invoice.sections.basic_info.title'))
                ->schema([
                    TextInput::make('invoice_number')
                        ->label(__('create_invoice.fields.invoice_number.label'))
                        ->placeholder(__('create_invoice.fields.invoice_number.placeholder'))
                        ->helperText(__('create_invoice.fields.invoice_number.helper')),

                    DatePicker::make('issue_date')
                        ->label(__('create_invoice.fields.issue_date.label'))
                        ->required()
                        ->default(now()),

                    DatePicker::make('due_date')
                        ->label(__('create_invoice.fields.due_date.label'))
                        ->required()
                        ->default(now()->addDays(30)),

                    TextInput::make('trading_place')
                        ->label(__('create_invoice.fields.trading_place.label'))
                        ->default('Beograd'),

                    Select::make('currency')
                        ->label(__('create_invoice.fields.currency.label'))
                        ->options([
                            'RSD' => __('create_invoice.currencies.RSD'),
                            'EUR' => __('create_invoice.currencies.EUR'),
                            'USD' => __('create_invoice.currencies.USD'),
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
                        ->label(__('create_invoice.fields.description.label'))
                        ->nullable()
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make(__('create_invoice.sections.invoice_items.title'))
                ->description(__('create_invoice.sections.invoice_items.description'))
                ->schema([
                    Repeater::make('invoice_items')
                        ->label(__('create_invoice.fields.invoice_items.label'))
                        ->schema([
                            Select::make('type')
                                ->label(__('create_invoice.fields.invoice_items.type'))
                                ->selectablePlaceholder(false)
                                ->options([
                                    'service' => __('create_invoice.item_types.service'),
                                    'product' => __('create_invoice.item_types.product'),
                                ])
                                ->default('service')
                                ->columnSpan(2)
                                ->required(),

                            TextInput::make('description')
                                ->label(__('create_invoice.fields.invoice_items.description'))
                                ->required()
                                ->columnSpan(3),

                            Select::make('unit')
                                ->label(__('create_invoice.fields.invoice_items.unit'))
                                ->selectablePlaceholder(false)
                                ->options([
                                    'kom' => __('create_invoice.units.kom'),
                                    'sat' => __('create_invoice.units.sat'),
                                    'm' => __('create_invoice.units.m'),
                                    'm2' => __('create_invoice.units.m2'),
                                    'm3' => __('create_invoice.units.m3'),
                                    'kg' => __('create_invoice.units.kg'),
                                    'l' => __('create_invoice.units.l'),
                                    'pak' => __('create_invoice.units.pak'),
                                    'reč' => __('create_invoice.units.reč'),
                                    'dan' => __('create_invoice.units.dan')
                                ]),

                            TextInput::make('quantity')
                                ->label(__('create_invoice.fields.invoice_items.quantity'))
                                ->numeric()
                                ->default(1)
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($state, $set, $get) {
                                    $this->calculateItemTotal($set, $get);
                                })
                                ->columnSpan(1),

                            TextInput::make('unit_price')
                                ->label(__('create_invoice.fields.invoice_items.unit_price'))
                                ->numeric()
                                ->step(0.01)
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($state, $set, $get) {
                                    $this->calculateItemTotal($set, $get);
                                })
                                ->columnSpan(2),

                            TextInput::make('discount_value')
                                ->label(__('create_invoice.fields.invoice_items.discount'))
                                ->numeric()
                                ->step(0.01)
                                ->default(0)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($state, $set, $get) {
                                    $this->calculateItemTotal($set, $get);
                                })
                                ->columnSpan(1),

                            Select::make('discount_type')
                                ->label(__('create_invoice.fields.invoice_items.discount_type'))
                                ->options(function () {
                                    $currency = $this->data['currency'] ?? 'RSD';
                                    return [
                                        'percent' => __('create_invoice.discount_types.percent'),
                                        'fixed' => __('create_invoice.discount_types.fixed', ['currency' => $currency]),
                                    ];
                                })
                                ->default('percent')
                                ->live()
                                ->afterStateUpdated(function ($state, $set, $get) {
                                    $this->calculateItemTotal($set, $get);
                                })
                                ->columnSpan(1),

                            TextInput::make('total')
                                ->label(__('create_invoice.fields.invoice_items.total'))
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
                        ->label(__('create_invoice.fields.invoice_total'))
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

    public function createInvoice(string $status): void
    {
        // Validate form data
        $this->form->validate();
        
        $data = $this->data;
        
        // Debug: Log the form data
        \Log::info('Form data:', $data);
        
        // Check if client_id is present
        if (!isset($data['client_id']) || empty($data['client_id'])) {
            Notification::make()
                ->title(__('create_invoice.notifications.error_no_client.title'))
                ->body(__('create_invoice.notifications.error_no_client.body'))
                ->danger()
                ->send();
            return;
        }
        
        $invoiceData = [
            'user_id' => Auth::id(),
            'client_id' => $data['client_id'],
            'invoice_type' => $data['invoice_type'],
            'invoice_document_type' => 'faktura',
            'issue_date' => $data['issue_date'],
            'due_date' => $data['due_date'],
            'trading_place' => $data['trading_place'],
            'currency' => $data['currency'],
            'description' => $data['description'],
            'status' => $status,
            'amount' => 0, // Will be updated automatically after items are created
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
            
            // Update invoice amount after all items are created
            $invoice->updateAmount();
        }

        // Show different notifications based on action
        $notificationKey = match($status) {
            'issued' => 'issued',
            'sent' => 'sent',
            default => 'saved'
        };

        Notification::make()
            ->title(__("create_invoice.notifications.{$notificationKey}.title"))
            ->body(__("create_invoice.notifications.{$notificationKey}.body", [
                'number' => $invoice->invoice_number
            ]))
            ->success()
            ->send();

        $this->redirect('/admin/invoices/'.$invoice->id.'/edit');
    }
    
    // Keep the old create method for backward compatibility
    public function create(): void
    {
        $this->createInvoice('in_preparation');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('create_invoice.actions.save'))
                ->icon('heroicon-o-document')
                ->color('gray')
                ->action('saveAsDraft'),

            Action::make('issue')
                ->label(__('create_invoice.actions.issue'))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->action('issueInvoice'),

            Action::make('send')
                ->label(__('create_invoice.actions.issue_and_send'))
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
