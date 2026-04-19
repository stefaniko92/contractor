<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Filament\Resources\Invoices\InvoiceResource;
use App\Helpers\FilamentHelper;
use App\Models\InvoiceItem;
use App\Services\SefService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print')
                ->label('Štampaj')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn () => route('invoices.print', $this->record))
                ->openUrlInNewTab(),

            Action::make('download')
                ->label('Preuzmi PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->url(fn () => route('invoices.download', $this->record))
                ->openUrlInNewTab(),

            Action::make('mark_as_sent')
                ->label('Označi kao poslatu')
                ->icon('heroicon-o-paper-airplane')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Označi fakturu kao poslatu')
                ->modalDescription(fn () => "Da li ste sigurni da želite da označite fakturu {$this->record->invoice_number} kao poslatu?")
                ->visible(fn () => ! $this->record->is_storno && $this->record->status !== 'sent')
                ->action(function () {
                    $this->record->update(['status' => 'sent']);

                    Notification::make()
                        ->title('Faktura je označena kao poslata')
                        ->body("Status fakture {$this->record->invoice_number} je uspešno promenjen u \"Poslana\".")
                        ->success()
                        ->send();
                }),

            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Calculate total amount from invoice items
        $totalAmount = 0;
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                if (isset($item['amount']) && is_numeric($item['amount'])) {
                    $totalAmount += (float) $item['amount'];
                }
            }
        }

        $data['amount'] = $totalAmount;

        return $data;
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Faktura je uspešno ažurirana')
            ->body('Sve izmene su sačuvane.');
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->label(__('actions.save'));
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label(__('actions.cancel'));
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
            $this->getCancelFormAction(),

            // Additional invoice actions
            Action::make('enter_payment')
                ->label('Unesi plaćanje')
                ->icon('heroicon-o-currency-dollar')
                ->color('success')
                ->visible(fn () => ! $this->record->is_storno)
                ->form([
                    DatePicker::make('payment_date')
                        ->label('Datum plaćanja')
                        ->default(now())
                        ->required(),
                    TextInput::make('payment_amount')
                        ->label('Iznos plaćanja')
                        ->numeric()
                        ->step(0.01)
                        ->required()
                        ->helperText(fn () => 'Ukupan iznos fakture: '.number_format($this->record->amount, 2).' '.$this->record->currency),
                ])
                ->fillForm(fn () => [
                    'payment_date' => now(),
                    'payment_amount' => $this->record->amount,
                ])
                ->action(function (array $data) {
                    $paymentAmount = (float) $data['payment_amount'];
                    $invoiceAmount = (float) $this->record->amount;

                    if ($paymentAmount >= $invoiceAmount) {
                        $this->record->update(['status' => 'charged']);
                        $statusMessage = 'Status fakture promenjen na "Naplaćena"';
                    } else {
                        $this->record->update(['status' => 'uncharged']);
                        $statusMessage = 'Status fakture promenjen na "Nenaplaćena" (delimično plaćanje)';
                    }

                    Notification::make()
                        ->title('Plaćanje zabeleženo')
                        ->body('Plaćanje od '.number_format($paymentAmount, 2).' '.$this->record->currency." je uspešno zabeleženo za fakturu {$this->record->invoice_number}. ".$statusMessage)
                        ->success()
                        ->send();
                }),

            Action::make('copy')
                ->label('Kopiraj')
                ->icon('heroicon-o-document-duplicate')
                ->color('gray')
                ->action(fn () => redirect()->to(
                    '/admin/create-invoice-page?'.http_build_query([
                        'copy_from_invoice' => $this->record->id,
                    ])
                )),

            Action::make('storno')
                ->label(__('actions.storno'))
                ->icon('heroicon-o-x-mark')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Storniraj fakturu')
                ->modalDescription(fn () => "Da li ste sigurni da želite da stornirate fakturu {$this->record->invoice_number}? Biće kreirana nova storno faktura sa negativnim iznosima u skladu sa srpskim zakonskim propisima. Obe fakture će biti zabeležene u knjizi prihoda.")
                ->modalSubmitActionLabel(__('actions.storno'))
                ->modalIcon('heroicon-o-exclamation-triangle')
                ->visible(fn () => ! $this->record->is_storno && $this->record->status !== 'in_preparation' && $this->record->stornoInvoices()->count() === 0)
                ->action(function () {
                    $stornoInvoice = \App\Models\Invoice::create([
                        'user_id' => $this->record->user_id,
                        'client_id' => $this->record->client_id,
                        'invoice_type' => $this->record->invoice_type,
                        'invoice_document_type' => $this->record->invoice_document_type,
                        'issue_date' => now(),
                        'due_date' => now()->addDays(30),
                        'trading_place' => $this->record->trading_place,
                        'currency' => $this->record->currency,
                        'description' => 'Storno fakture '.$this->record->invoice_number.' od '.$this->record->issue_date->format('d.m.Y'),
                        'status' => 'storned',
                        'amount' => -$this->record->amount,
                        'is_storno' => true,
                        'original_invoice_id' => $this->record->id,
                        'original_invoice_number' => $this->record->invoice_number,
                        'original_invoice_date' => $this->record->issue_date,
                    ]);

                    foreach ($this->record->items as $item) {
                        InvoiceItem::create([
                            'invoice_id' => $stornoInvoice->id,
                            'title' => $item->title,
                            'description' => 'Storno: '.$item->description,
                            'type' => $item->type,
                            'unit' => $item->unit,
                            'quantity' => $item->quantity,
                            'unit_price' => -$item->unit_price,
                            'discount_value' => $item->discount_value,
                            'discount_type' => $item->discount_type,
                            'amount' => -$item->amount,
                            'currency' => $item->currency,
                        ]);
                    }

                    $this->record->update(['status' => 'charged']);

                    Notification::make()
                        ->title('Storno faktura kreirana')
                        ->body("Kreirana je storno faktura {$stornoInvoice->invoice_number} za originalnu fakturu {$this->record->invoice_number}. Obe fakture su zabeležene u knjizi prihoda u skladu sa zakonskim propisima.")
                        ->success()
                        ->send();
                }),

            Action::make('send_to_efaktura')
                ->label('Pošalji na eFakturu')
                ->icon('heroicon-o-envelope')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Slanje fakture putem eFaktura sistema')
                ->modalDescription('Da li sigurno želiš da pošalješ fakturu putem portala eFaktura?')
                ->modalContent(fn () => view('filament.modals.efaktura-confirmation', [
                    'invoice' => $this->record,
                ]))
                ->form([
                    DatePicker::make('due_date')
                        ->label('Odaberi datum dospeća fakture:')
                        ->default(fn () => $this->record->due_date ?? now()->addDays(30))
                        ->required()
                        ->native(false)
                        ->helperText('Datum kada faktura dospećava na naplatu'),
                ])
                ->fillForm(fn () => [
                    'due_date' => $this->record->due_date ?? now()->addDays(30),
                ])
                ->modalSubmitActionLabel(__('actions.yes'))
                ->modalCancelActionLabel(__('actions.no'))
                ->modalIcon('heroicon-o-envelope')
                ->modalWidth(FilamentHelper::getModalSizeForContext('efaktura_modal'))
                ->visible(fn () => ! $this->record->is_storno && (
                    $this->record->efakturaInvoice === null ||
                    $this->record->efakturaInvoice->status === 'failed'
                ))
                ->action(function (array $data) {
                    $dueDate = $data['due_date'] ?? now()->addDays(30);

                    if ($dueDate !== $this->record->due_date) {
                        $this->record->update(['due_date' => $dueDate]);
                    }

                    $sefService = SefService::forAuthenticatedUser();
                    $availabilityStatus = $sefService->getAvailabilityStatus();

                    if (! $availabilityStatus['available']) {
                        Notification::make()
                            ->title('SEF nije dostupan')
                            ->body($availabilityStatus['message'])
                            ->warning()
                            ->send();

                        Log::warning('SEF not available for invoice sending', [
                            'invoice_id' => $this->record->id,
                            'invoice_number' => $this->record->invoice_number,
                            'availability_status' => $availabilityStatus,
                        ]);

                        return;
                    }

                    // Check if client is verified or has bypass enabled
                    if (! $this->record->client->efaktura_verified && ! $this->record->client->allow_efaktura_bypass) {
                        Notification::make()
                            ->title('Klijent nije verifikovan')
                            ->body('Klijent još nije proverен u eFaktura sistemu. Molimo sačekajte automatsku verifikaciju ili pokrenite komandu ručno.')
                            ->warning()
                            ->send();

                        Log::warning('Client not verified in eFaktura', [
                            'invoice_id' => $this->record->id,
                            'invoice_number' => $this->record->invoice_number,
                            'client_id' => $this->record->client_id,
                            'client_name' => $this->record->client->name,
                        ]);

                        return;
                    }

                    if ($this->record->client->efaktura_status !== 'active' && ! $this->record->client->allow_efaktura_bypass) {
                        Notification::make()
                            ->title('Klijent nije pronađen u eFaktura')
                            ->body('Ovaj klijent ne postoji u eFaktura sistemu. Ne možete poslati fakturu elektronski.')
                            ->danger()
                            ->send();

                        Log::warning('Client not found in eFaktura', [
                            'invoice_id' => $this->record->id,
                            'invoice_number' => $this->record->invoice_number,
                            'client_id' => $this->record->client_id,
                            'client_name' => $this->record->client->name,
                            'efaktura_status' => $this->record->client->efaktura_status,
                            'verification_error' => $this->record->client->efaktura_verification_error,
                        ]);

                        return;
                    }

                    try {
                        Log::info('eFaktura send action triggered', [
                            'invoice_id' => $this->record->id,
                            'invoice_number' => $this->record->invoice_number,
                            'due_date' => is_string($dueDate) ? $dueDate : $dueDate->format('Y-m-d'),
                            'sef_configured' => true,
                        ]);

                        $xmlContent = $this->record->generateUblXml();

                        Log::info('UBL XML generated successfully', [
                            'invoice_id' => $this->record->id,
                            'xml_length' => strlen($xmlContent),
                        ]);

                        $response = $sefService->sendInvoice($xmlContent, 'Yes');

                        if (isset($response['error'])) {
                            // Update existing record or create new one
                            \App\Models\EfakturaInvoice::updateOrCreate(
                                ['invoice_id' => $this->record->id],
                                [
                                    'user_id' => $this->record->user_id,
                                    'status' => 'failed',
                                    'last_error' => $response['error'],
                                    'last_error_at' => now(),
                                    'sef_response' => $response,
                                ]
                            );

                            Notification::make()
                                ->title('Greška pri slanju fakture')
                                ->body($response['error'])
                                ->danger()
                                ->send();

                            Log::error('Failed to send invoice to eFaktura', [
                                'invoice_id' => $this->record->id,
                                'invoice_number' => $this->record->invoice_number,
                                'error' => $response,
                            ]);

                            return;
                        }

                        // Update existing record or create new one
                        $efakturaInvoice = \App\Models\EfakturaInvoice::updateOrCreate(
                            ['invoice_id' => $this->record->id],
                            [
                                'user_id' => $this->record->user_id,
                                'sef_invoice_id' => $response['SalesInvoiceId'] ?? $response['InvoiceId'] ?? $response['invoiceId'] ?? $response['id'] ?? null,
                                'sef_invoice_number' => $response['InvoiceNumber'] ?? $response['invoiceNumber'] ?? null,
                                'sef_request_id' => $response['RequestId'] ?? $response['requestId'] ?? null,
                                'status' => 'sent',
                                'sent_at' => now(),
                                'last_error' => null,
                                'last_error_at' => null,
                                'sef_response' => $response,
                                'status_history' => [
                                    [
                                        'from' => $this->record->efakturaInvoice?->status ?? 'draft',
                                        'to' => 'sent',
                                        'changed_at' => now()->toISOString(),
                                        'data' => $response,
                                    ],
                                ],
                            ]
                        );

                        Notification::make()
                            ->title('eFaktura uspešno poslata')
                            ->body("Faktura {$this->record->invoice_number} je uspešno poslata na eFaktura sistem. Datum dospeća: ".(is_string($dueDate) ? $dueDate : $dueDate->format('d.m.Y')).'.')
                            ->success()
                            ->send();

                        Log::info('Invoice sent to eFaktura successfully', [
                            'invoice_id' => $this->record->id,
                            'invoice_number' => $this->record->invoice_number,
                            'efaktura_invoice_id' => $efakturaInvoice->id,
                            'sef_response' => $response,
                        ]);
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Greška pri generisanju ili slanju fakture')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();

                        Log::error('Exception during invoice sending', [
                            'invoice_id' => $this->record->id,
                            'invoice_number' => $this->record->invoice_number,
                            'exception' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                    }
                }),

            Action::make('refresh_efaktura_status')
                ->label('Osvježi eFaktura status')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->visible(fn () => $this->record->efakturaInvoice !== null)
                ->requiresConfirmation()
                ->modalHeading('Osvježi status fakture na eFaktura sistemu')
                ->modalDescription('Da li želiš da proveriš trenutni status ove fakture na eFaktura portalu?')
                ->modalSubmitActionLabel(__('actions.refresh'))
                ->action(function () {
                    $efakturaInvoice = $this->record->efakturaInvoice;

                    if (! $efakturaInvoice) {
                        Notification::make()
                            ->title('Greška')
                            ->body('Ova faktura nije poslata na eFaktura sistem.')
                            ->warning()
                            ->send();

                        return;
                    }

                    try {
                        $response = $efakturaInvoice->refreshStatus();

                        if (isset($response['error'])) {
                            Notification::make()
                                ->title('Greška pri osvježavanju statusa')
                                ->body($response['error'])
                                ->danger()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('Status osvježen')
                            ->body("Trenutni status fakture na eFaktura sistemu: {$efakturaInvoice->status}")
                            ->success()
                            ->send();

                        Log::info('eFaktura status refreshed', [
                            'invoice_id' => $this->record->id,
                            'efaktura_invoice_id' => $efakturaInvoice->id,
                            'status' => $efakturaInvoice->status,
                            'response' => $response,
                        ]);
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Greška pri osvježavanju statusa')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();

                        Log::error('Exception during status refresh', [
                            'invoice_id' => $this->record->id,
                            'efaktura_invoice_id' => $efakturaInvoice->id,
                            'exception' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                    }
                }),
        ];
    }
}
