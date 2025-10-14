<?php

namespace App\Filament\Resources\Invoices\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use App\Models\Invoice;
use App\Models\InvoiceItem;

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
                    ->sortable(),
                
                TextColumn::make('invoice_number')
                    ->label('Broj fakture')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->is_storno) {
                            return $state . ' (STORNO)';
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
                        return number_format($state, 2) . ' ' . $record->currency;
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
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->icon('heroicon-o-pencil')
                    ->visible(function ($record) {
                        // Don't allow editing of storno invoices
                        return !$record->is_storno;
                    }),
                
                ActionGroup::make([
                    Action::make('print')
                        ->label('Štampaj')
                        ->icon('heroicon-o-printer')
                        ->color('gray')
                        ->url(fn ($record): string => route('invoices.print', $record))
                        ->openUrlInNewTab(),

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
                                '/admin/create-invoice-page?' . http_build_query([
                                    'copy_from_invoice' => $record->id,
                                ])
                            );
                        }),
                    
                    Action::make('issue')
                        ->label('Izdaj fakturu')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Izdaj fakturu')
                        ->modalDescription(function ($record) {
                            return "Da li želite da izdajete fakturu {$record->invoice_number}? Status će biti promenjen na 'Izdata'.";
                        })
                        ->modalSubmitActionLabel('Izdaj')
                        ->modalIcon('heroicon-o-check-circle')
                        ->visible(function ($record) {
                            // Only show issue action for invoices in preparation
                            return !$record->is_storno && $record->status === 'in_preparation';
                        })
                        ->action(function ($record) {
                            $record->update(['status' => 'issued']);
                            
                            Notification::make()
                                ->title('Faktura izdata')
                                ->body("Faktura {$record->invoice_number} je uspešno izdata.")
                                ->success()
                                ->send();
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
                            return !$record->is_storno && $record->status !== 'in_preparation' && $record->stornoInvoices()->count() === 0;
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
                                'description' => 'Storno fakture ' . $record->invoice_number . ' od ' . $record->issue_date->format('d.m.Y'),
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
                                    'description' => 'Storno: ' . $item->description,
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
                            return !$record->is_storno;
                        })
                        ->action(function () {
                            // TODO: Implement send functionality
                        }),
                    
                    Action::make('enter_payment')
                        ->label('Unesi plaćanje')
                        ->icon('heroicon-o-currency-dollar')
                        ->color('success')
                        ->visible(function ($record) {
                            // Don't show payment entry for storno invoices
                            return !$record->is_storno;
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
                                    return "Ukupan iznos fakture: " . number_format($record->amount, 2) . " " . $record->currency;
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
                                ->body("Plaćanje od " . number_format($paymentAmount, 2) . " " . $record->currency . " je uspešno zabeleženo za fakturu {$record->invoice_number}. " . $statusMessage)
                                ->success()
                                ->send();
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
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}