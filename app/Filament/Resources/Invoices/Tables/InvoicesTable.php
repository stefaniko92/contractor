<?php

namespace App\Filament\Resources\Invoices\Tables;

use App\Helpers\FilamentHelper;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\SefService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // Default visible columns
                TextColumn::make('client.company_name')
                    ->label('Klijent')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->width('250px'),

                TextColumn::make('invoice_number')
                    ->label('Broj fakture')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->is_storno) {
                            return $state.' (STORNO)';
                        }

                        return $state;
                    })
                    ->color(function ($record) {
                        return $record->is_storno ? 'danger' : null;
                    }),

                TextColumn::make('issue_date')
                    ->label('Datum')
                    ->date('d.m.Y')
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Iznos')
                    ->numeric(2)
                    ->sortable()
                    ->formatStateUsing(function ($state, $record) {
                        return number_format($state, 2).' '.$record->currency;
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sent' => 'info',
                        'issued' => 'success',
                        'in_preparation' => 'warning',
                        'charged' => 'success',
                        'uncharged' => 'danger',
                        'storned' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'sent' => 'Poslana',
                        'issued' => 'Izdata',
                        'in_preparation' => 'U pripremi',
                        'charged' => 'Naplaćena',
                        'uncharged' => 'Nenaplaćena',
                        'storned' => 'Stornirana',
                        default => $state,
                    }),

                TextColumn::make('efakturaInvoice.status')
                    ->label('eFaktura Status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'sent' => 'info',
                        'delivered' => 'primary',
                        'accepted' => 'success',
                        'rejected' => 'danger',
                        'cancelled' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'sent' => 'Poslato',
                        'delivered' => 'Dostavljeno',
                        'accepted' => 'Prihvaćeno',
                        'rejected' => 'Odbijeno',
                        'cancelled' => 'Otkazano',
                        'failed' => 'Neuspelo',
                        null => 'Nije poslato',
                        default => $state,
                    })
                    ->default('Nije poslato')
                    ->tooltip(function ($record) {
                        if ($record->efakturaInvoice) {
                            $sent = $record->efakturaInvoice->sent_at?->format('d.m.Y H:i');
                            $updated = $record->efakturaInvoice->updated_at?->format('d.m.Y H:i');

                            return "Poslato: {$sent}\nAžurirano: {$updated}";
                        }

                        return null;
                    }),

                // Additional toggleable columns (hidden by default)
                TextColumn::make('invoice_document_type')
                    ->label('Tip dokumenta')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'faktura' => 'success',
                        'profaktura' => 'info',
                        'avansna_faktura' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'faktura' => 'Faktura',
                        'profaktura' => 'Profaktura',
                        'avansna_faktura' => 'Avansna Faktura',
                        default => $state,
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('invoice_type')
                    ->label('Tip fakture')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'domestic' => 'success',
                        'foreign' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'domestic' => 'Domaća',
                        'foreign' => 'Inostrana',
                        default => $state,
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('due_date')
                    ->label('Datum dospeća')
                    ->date('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('currency')
                    ->label('Valuta')
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('trading_place')
                    ->label('Mesto prometa')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('description')
                    ->label('Opis')
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 30) {
                            return null;
                        }

                        return $state;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Kreirana')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Ažurirana')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'sent' => 'Poslana',
                        'issued' => 'Izdata',
                        'in_preparation' => 'U pripremi',
                        'charged' => 'Naplaćena',
                        'uncharged' => 'Nenaplaćena',
                        'storned' => 'Stornirana',
                    ])
                    ->multiple()
                    ->searchable(),

                SelectFilter::make('currency')
                    ->label('Valuta')
                    ->options([
                        'RSD' => 'RSD',
                        'EUR' => 'EUR',
                        'USD' => 'USD',
                    ])
                    ->multiple()
                    ->searchable(),

                SelectFilter::make('year')
                    ->label('Godina')
                    ->options(function () {
                        $currentYear = now()->year;
                        $years = range($currentYear - 5, $currentYear + 1);

                        return array_combine($years, $years);
                    })
                    ->query(function ($query, $data) {
                        if (! empty($data['value'])) {
                            return $query->whereYear('issue_date', $data['value']);
                        }

                        return $query;
                    })
                    ->searchable(),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->defaultPaginationPageOption(25)
            ->recordActions([
                Action::make('print')
                    ->label('Štampaj')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->url(fn ($record): string => route('invoices.print', $record))
                    ->openUrlInNewTab(),

                Action::make('enter_payment')
                    ->label('Unesi plaćanje')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('success')
                    ->visible(function ($record) {
                        // Don't show payment entry for storno invoices
                        return ! $record->is_storno;
                    })
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
                            ->helperText(function ($record) {
                                return 'Ukupan iznos fakture: '.number_format($record->amount, 2).' '.$record->currency;
                            }),
                    ])
                    ->fillForm(function ($record) {
                        return [
                            'payment_date' => now(),
                            'payment_amount' => $record->amount,
                        ];
                    })
                    ->action(function (array $data, $record) {
                        // Update invoice status based on payment amount
                        $paymentAmount = (float) $data['payment_amount'];
                        $invoiceAmount = (float) $record->amount;

                        if ($paymentAmount >= $invoiceAmount) {
                            $record->update(['status' => 'charged']);
                            $statusMessage = 'Status fakture promenjen na "Naplaćena"';
                        } else {
                            $record->update(['status' => 'uncharged']);
                            $statusMessage = 'Status fakture promenjen na "Nenaplaćena" (delimično plaćanje)';
                        }

                        Notification::make()
                            ->title('Plaćanje zabeleženo')
                            ->body('Plaćanje od '.number_format($paymentAmount, 2).' '.$record->currency." je uspešno zabeleženo za fakturu {$record->invoice_number}. ".$statusMessage)
                            ->success()
                            ->send();
                    }),

                ActionGroup::make([
                    EditAction::make()
                        ->label('Uredi')
                        ->icon('heroicon-o-pencil')
                        ->visible(function ($record) {
                            // Don't allow editing of storno invoices
                            return ! $record->is_storno;
                        }),

                    Action::make('download')
                        ->label('Preuzmi PDF')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('gray')
                        ->url(fn ($record): string => route('invoices.download', $record))
                        ->openUrlInNewTab(),

                    Action::make('copy')
                        ->label('Kopiraj')
                        ->icon('heroicon-o-document-duplicate')
                        ->color('gray')
                        ->action(function ($record) {
                            // Redirect to invoice creation form with prefilled data
                            return redirect()->to(
                                '/admin/create-invoice-page?'.http_build_query([
                                    'copy_from_invoice' => $record->id,
                                ])
                            );
                        }),

                    Action::make('storno')
                        ->label('Storniraj')
                        ->icon('heroicon-o-x-mark')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Storniraj fakturu')
                        ->modalDescription(function ($record) {
                            return "Da li ste sigurni da želite da stornirate fakturu {$record->invoice_number}? Biće kreirana nova storno faktura sa negativnim iznosima u skladu sa srpskim zakonskim propisima. Obe fakture će biti zabeležene u knjizi prihoda.";
                        })
                        ->modalSubmitActionLabel('Storniraj')
                        ->modalIcon('heroicon-o-exclamation-triangle')
                        ->visible(function ($record) {
                            // Only show storno action for issued invoices that are not storno invoices themselves and don't already have a storno
                            return ! $record->is_storno && $record->status !== 'in_preparation' && $record->stornoInvoices()->count() === 0;
                        })
                        ->action(function ($record) {
                            // Create storno (reversal) invoice with negative amounts
                            $stornoInvoice = Invoice::create([
                                'user_id' => $record->user_id,
                                'client_id' => $record->client_id,
                                'invoice_type' => $record->invoice_type,
                                'invoice_document_type' => $record->invoice_document_type,
                                'issue_date' => now(),
                                'due_date' => now()->addDays(30),
                                'trading_place' => $record->trading_place,
                                'currency' => $record->currency,
                                'description' => 'Storno fakture '.$record->invoice_number.' od '.$record->issue_date->format('d.m.Y'),
                                'status' => 'storned',
                                'amount' => -$record->amount, // Negative amount
                                'is_storno' => true,
                                'original_invoice_id' => $record->id,
                                'original_invoice_number' => $record->invoice_number,
                                'original_invoice_date' => $record->issue_date,
                                // invoice_number will be auto-generated by the model's boot method
                            ]);

                            // Create negative invoice items
                            foreach ($record->items as $item) {
                                InvoiceItem::create([
                                    'invoice_id' => $stornoInvoice->id,
                                    'title' => $item->title,
                                    'description' => 'Storno: '.$item->description,
                                    'type' => $item->type,
                                    'unit' => $item->unit,
                                    'quantity' => $item->quantity,
                                    'unit_price' => -$item->unit_price, // Negative unit price
                                    'discount_value' => $item->discount_value,
                                    'discount_type' => $item->discount_type,
                                    'amount' => -$item->amount, // Negative amount
                                    'currency' => $item->currency,
                                ]);
                            }

                            // Update original invoice status to charged (since stornoing implies it was paid)
                            $record->update(['status' => 'charged']);

                            Notification::make()
                                ->title('Storno faktura kreirana')
                                ->body("Kreirana je storno faktura {$stornoInvoice->invoice_number} za originalnu fakturu {$record->invoice_number}. Obe fakture su zabeležene u knjizi prihoda u skladu sa zakonskim propisima.")
                                ->success()
                                ->send();
                        }),

                    Action::make('send')
                        ->label('Pošalji')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('info')
                        ->visible(function ($record) {
                            // Don't show send action for storno invoices
                            return ! $record->is_storno;
                        })
                        ->action(function () {
                            // TODO: Implement send functionality
                        }),

                    Action::make('send_to_efaktura')
                        ->label('eFaktura')
                        ->icon('heroicon-o-envelope')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->modalHeading('Slanje fakture putem eFaktura sistema')
                        ->modalDescription('Da li sigurno želiš da pošalješ fakturu putem portala eFaktura?')
                        ->modalContent(function ($record) {
                            return view('filament.modals.efaktura-confirmation', [
                                'invoice' => $record,
                            ]);
                        })
                        ->form([
                            DatePicker::make('due_date')
                                ->label('Odaberi datum dospeća fakture:')
                                ->default(fn ($record) => $record->due_date ?? now()->addDays(30))
                                ->required()
                                ->native(false)
                                ->helperText('Datum kada faktura dospećava na naplatu'),
                        ])
                        ->fillForm(function ($record) {
                            return [
                                'due_date' => $record->due_date ?? now()->addDays(30),
                            ];
                        })
                        ->modalSubmitActionLabel('Da')
                        ->modalCancelActionLabel('Ne')
                        ->modalIcon('heroicon-o-envelope')
                        ->modalWidth(FilamentHelper::getModalSizeForContext('efaktura_modal'))
                        ->visible(function ($record) {
                            // Only show if not sent to eFaktura yet and not a storno invoice
                            return ! $record->is_storno && $record->efakturaInvoice === null;
                        })
                        ->action(function (array $data, $record) {
                            $dueDate = $data['due_date'] ?? now()->addDays(30);

                            // Update the due date if it was changed
                            if ($dueDate !== $record->due_date) {
                                $record->update(['due_date' => $dueDate]);
                            }

                            // Initialize SEF service for the authenticated user
                            $sefService = SefService::forAuthenticatedUser();

                            // Check if SEF is configured and available
                            $availabilityStatus = $sefService->getAvailabilityStatus();

                            if (! $availabilityStatus['available']) {
                                Notification::make()
                                    ->title('SEF nije dostupan')
                                    ->body($availabilityStatus['message'])
                                    ->warning()
                                    ->send();

                                Log::warning('SEF not available for invoice sending', [
                                    'invoice_id' => $record->id,
                                    'invoice_number' => $record->invoice_number,
                                    'availability_status' => $availabilityStatus,
                                ]);

                                return;
                            }

                            // Check if client is verified in eFaktura system
                            if (! $record->client->efaktura_verified) {
                                Notification::make()
                                    ->title('Klijent nije verifikovan')
                                    ->body('Klijent još nije proverен u eFaktura sistemu. Molimo sačekajte automatsku verifikaciju ili pokrenite komandu ručno.')
                                    ->warning()
                                    ->send();

                                Log::warning('Client not verified in eFaktura', [
                                    'invoice_id' => $record->id,
                                    'invoice_number' => $record->invoice_number,
                                    'client_id' => $record->client_id,
                                    'client_name' => $record->client->name,
                                ]);

                                return;
                            }

                            if ($record->client->efaktura_status !== 'active') {
                                Notification::make()
                                    ->title('Klijent nije pronađen u eFaktura')
                                    ->body('Ovaj klijent ne postoji u eFaktura sistemu. Ne možete poslati fakturu elektronski.')
                                    ->danger()
                                    ->send();

                                Log::warning('Client not found in eFaktura', [
                                    'invoice_id' => $record->id,
                                    'invoice_number' => $record->invoice_number,
                                    'client_id' => $record->client_id,
                                    'client_name' => $record->client->name,
                                    'efaktura_status' => $record->client->efaktura_status,
                                    'verification_error' => $record->client->efaktura_verification_error,
                                ]);

                                return;
                            }

                            // Generate UBL XML from invoice
                            try {
                                Log::info('eFaktura send action triggered', [
                                    'invoice_id' => $record->id,
                                    'invoice_number' => $record->invoice_number,
                                    'due_date' => is_string($dueDate) ? $dueDate : $dueDate->format('Y-m-d'),
                                    'sef_configured' => true,
                                ]);

                                $xmlContent = $record->generateUblXml();

                                Log::info('UBL XML generated successfully', [
                                    'invoice_id' => $record->id,
                                    'xml_length' => strlen($xmlContent),
                                ]);

                                $response = $sefService->sendInvoice($xmlContent, 'SendToCir');

                                if (isset($response['error'])) {
                                    // Store failed attempt
                                    \App\Models\EfakturaInvoice::create([
                                        'invoice_id' => $record->id,
                                        'user_id' => $record->user_id,
                                        'status' => 'failed',
                                        'last_error' => $response['error'],
                                        'last_error_at' => now(),
                                        'sef_response' => $response,
                                    ]);

                                    Notification::make()
                                        ->title('Greška pri slanju fakture')
                                        ->body($response['error'])
                                        ->danger()
                                        ->send();

                                    Log::error('Failed to send invoice to eFaktura', [
                                        'invoice_id' => $record->id,
                                        'invoice_number' => $record->invoice_number,
                                        'error' => $response,
                                    ]);

                                    return;
                                }

                                // Store successful send in efaktura_invoices table
                                $efakturaInvoice = \App\Models\EfakturaInvoice::create([
                                    'invoice_id' => $record->id,
                                    'user_id' => $record->user_id,
                                    'sef_invoice_id' => $response['SalesInvoiceId'] ?? $response['InvoiceId'] ?? $response['invoiceId'] ?? $response['id'] ?? null,
                                    'sef_invoice_number' => $response['InvoiceNumber'] ?? $response['invoiceNumber'] ?? null,
                                    'sef_request_id' => $response['RequestId'] ?? $response['requestId'] ?? null,
                                    'status' => 'sent',
                                    'sent_at' => now(),
                                    'sef_response' => $response,
                                    'status_history' => [
                                        [
                                            'from' => 'draft',
                                            'to' => 'sent',
                                            'changed_at' => now()->toISOString(),
                                            'data' => $response,
                                        ],
                                    ],
                                ]);

                                Notification::make()
                                    ->title('eFaktura uspešno poslata')
                                    ->body("Faktura {$record->invoice_number} je uspešno poslata na eFaktura sistem. Datum dospeća: ".(is_string($dueDate) ? $dueDate : $dueDate->format('d.m.Y')).'.')
                                    ->success()
                                    ->send();

                                Log::info('Invoice sent to eFaktura successfully', [
                                    'invoice_id' => $record->id,
                                    'invoice_number' => $record->invoice_number,
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
                                    'invoice_id' => $record->id,
                                    'invoice_number' => $record->invoice_number,
                                    'exception' => $e->getMessage(),
                                    'trace' => $e->getTraceAsString(),
                                ]);
                            }
                        }),

                    Action::make('refresh_efaktura_status')
                        ->label('Osvježi eFaktura status')
                        ->icon('heroicon-o-arrow-path')
                        ->color('info')
                        ->visible(function ($record) {
                            // Only show if invoice has been sent to eFaktura
                            return $record->efakturaInvoice !== null;
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Osvježi status fakture na eFaktura sistemu')
                        ->modalDescription('Da li želiš da proveriš trenutni status ove fakture na eFaktura portalu?')
                        ->modalSubmitActionLabel('Osvježi')
                        ->action(function ($record) {
                            $efakturaInvoice = $record->efakturaInvoice;

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
                                    'invoice_id' => $record->id,
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
                                    'invoice_id' => $record->id,
                                    'efaktura_invoice_id' => $efakturaInvoice->id,
                                    'exception' => $e->getMessage(),
                                    'trace' => $e->getTraceAsString(),
                                ]);
                            }
                        }),

                    Action::make('delete')
                        ->label('Obriši')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Obriši fakturu')
                        ->modalDescription('Da li ste sigurni da želite da obrišete ovu fakturu? Ova akcija se ne može poništiti.')
                        ->modalSubmitActionLabel('Obriši')
                        ->action(function ($record) {
                            $record->delete();

                            Notification::make()
                                ->title('Faktura obrisana')
                                ->body("Faktura broj {$record->invoice_number} je uspešno obrisana.")
                                ->success()
                                ->send();
                        }),
                ])
                    ->label('Akcije')
                    ->icon('heroicon-o-ellipsis-vertical')
                    ->size('sm')
                    ->color('gray'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('mark_as_paid')
                        ->label('Označi kao plaćeno')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Označi fakture kao plaćene')
                        ->modalDescription('Da li sigurno želite da označite odabrane fakture kao plaćene? Datum fakture će biti korišćen kao datum plaćanja.')
                        ->modalSubmitActionLabel('Označi kao plaćeno')
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records) {
                            $count = 0;
                            foreach ($records as $record) {
                                if (! $record->is_storno && $record->status !== 'charged') {
                                    $record->update(['status' => 'charged']);
                                    $count++;
                                }
                            }

                            Notification::make()
                                ->title('Fakture označene kao plaćene')
                                ->body("Uspešno je označeno {$count} faktura/e kao plaćeno.")
                                ->success()
                                ->send();
                        }),

                    DeleteBulkAction::make()
                        ->label('Obriši')
                        ->requiresConfirmation()
                        ->modalHeading('Obriši fakture')
                        ->modalDescription('Da li ste sigurni da želite da obrišete odabrane fakture? Ova akcija se ne može poništiti.')
                        ->modalSubmitActionLabel('Obriši')
                        ->deselectRecordsAfterCompletion()
                        ->successNotification(
                            Notification::make()
                                ->title('Fakture obrisane')
                                ->body('Odabrane fakture su uspešno obrisane.')
                                ->success()
                        ),
                ]),
            ]);
    }
}
