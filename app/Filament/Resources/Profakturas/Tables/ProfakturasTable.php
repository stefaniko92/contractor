<?php

namespace App\Filament\Resources\Profakturas\Tables;

use App\Filament\Resources\AvansnaFakturas\AvansnaFakturaResource;
use App\Filament\Resources\Invoices\InvoiceResource;
use App\Models\Invoice;
use App\Services\InvoiceDocumentFlowService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class ProfakturasTable
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
                    ->label('Broj profakture')
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

                // Additional toggleable columns (hidden by default)
                TextColumn::make('invoice_type')
                    ->label('Tip profakture')
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

                Action::make('create_invoice')
                    ->label('Kreiraj fakturu')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Kreiraj fakturu od profakture')
                    ->modalDescription(function ($record) {
                        return "Da li želite da kreirate fakturu na osnovu profakture {$record->invoice_number}? Svi podaci će biti kopirani u novu fakturu.";
                    })
                    ->modalSubmitActionLabel(__('actions.create_invoice'))
                    ->modalIcon('heroicon-o-document-text')
                    ->visible(function ($record) {
                        // Only show for non-storno profakturas
                        return ! $record->is_storno;
                    })
                    ->action(function ($record) {
                        // Redirect to invoice creation form with prepopulated data
                        return redirect()->to(
                            '/admin/create-invoice-page?'.http_build_query([
                                'copy_from_profaktura' => $record->id,
                            ])
                        );
                    }),

                ActionGroup::make([
                    EditAction::make()
                        ->label('Uredi')
                        ->icon('heroicon-o-pencil'),

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
                        ->action(function () {
                            // TODO: Implement copy functionality
                        }),

                    Action::make('create_avans_invoice')
                        ->label('Kreiraj avansnu fakturu')
                        ->icon('heroicon-o-document-text')
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalHeading('Kreiraj avansnu fakturu od profakture')
                        ->modalDescription(function ($record) {
                            return "Da li želite da kreirate avansnu fakturu na osnovu profakture {$record->invoice_number}? Svi podaci će biti kopirani u novu avansnu fakturu.";
                        })
                        ->modalSubmitActionLabel(__('actions.create_advance_invoice'))
                        ->modalIcon('heroicon-o-document-text')
                        ->form([
                            TextInput::make('amount')
                                ->label('Iznos avansa')
                                ->numeric()
                                ->required()
                                ->minValue(0.01)
                                ->default(fn (Invoice $record): float => app(InvoiceDocumentFlowService::class)->remainingProformaAmount($record))
                                ->maxValue(fn (Invoice $record): float => app(InvoiceDocumentFlowService::class)->remainingProformaAmount($record))
                                ->suffix(fn (Invoice $record): string => $record->currency),
                        ])
                        ->visible(function ($record) {
                            return ! $record->is_storno
                                && app(InvoiceDocumentFlowService::class)->remainingProformaAmount($record) > 0;
                        })
                        ->action(function (array $data, Invoice $record) {
                            try {
                                $advance = app(InvoiceDocumentFlowService::class)->createAdvanceFromProforma(
                                    $record,
                                    (float) $data['amount'],
                                );

                                Notification::make()
                                    ->title('Avansna faktura je kreirana')
                                    ->body("Avansna faktura {$advance->invoice_number} je vezana za profakturu {$record->invoice_number}.")
                                    ->success()
                                    ->send();

                                return redirect()->to(AvansnaFakturaResource::getUrl('edit', ['record' => $advance]));
                            } catch (ValidationException $exception) {
                                Notification::make()
                                    ->title('Avansna faktura nije kreirana')
                                    ->body(implode(' ', $exception->errors()['amount'] ?? $exception->errors()['profaktura'] ?? []))
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Action::make('create_final_invoice')
                        ->label('Kreiraj konačnu fakturu')
                        ->icon('heroicon-o-document-check')
                        ->color('success')
                        ->modalHeading('Konačna faktura sa umanjenjem avansa')
                        ->modalDescription('Izaberi naplaćene avanse koji se odbijaju od ukupnog iznosa profakture.')
                        ->modalSubmitActionLabel('Kreiraj konačnu fakturu')
                        ->form([
                            CheckboxList::make('advance_invoice_ids')
                                ->label('Naplaćene avansne fakture')
                                ->options(function (Invoice $record): array {
                                    $flow = app(InvoiceDocumentFlowService::class);

                                    return $record->advanceInvoices()
                                        ->where('status', 'charged')
                                        ->get()
                                        ->mapWithKeys(fn (Invoice $advance): array => [
                                            $advance->id => "{$advance->invoice_number} - ".number_format($flow->availableAdvanceAmount($advance), 2)." {$advance->currency}",
                                        ])
                                        ->all();
                                })
                                ->required(),
                        ])
                        ->visible(function (Invoice $record): bool {
                            return ! $record->is_storno
                                && $record->advanceInvoices()->where('status', 'charged')->exists();
                        })
                        ->action(function (array $data, Invoice $record) {
                            try {
                                $advances = Invoice::query()
                                    ->whereKey($data['advance_invoice_ids'])
                                    ->get();
                                $invoice = app(InvoiceDocumentFlowService::class)->createFinalInvoiceFromProforma($record, $advances);

                                Notification::make()
                                    ->title('Konačna faktura je kreirana')
                                    ->body("Faktura {$invoice->invoice_number} sadrži umanjenje za izabrane avanse.")
                                    ->success()
                                    ->send();

                                return redirect()->to(InvoiceResource::getUrl('edit', ['record' => $invoice]));
                            } catch (ValidationException $exception) {
                                Notification::make()
                                    ->title('Konačna faktura nije kreirana')
                                    ->body(implode(' ', $exception->errors()['advance_invoice_ids'] ?? $exception->errors()['profaktura'] ?? []))
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Action::make('send')
                        ->label('Pošalji')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('info')
                        ->action(function () {
                            Notification::make()
                                ->title('Profaktura se ne šalje na SEF')
                                ->body('SEF se koristi za fakture, avansne fakture i knjižna odobrenja.')
                                ->warning()
                                ->send();
                        }),

                    Action::make('delete')
                        ->label('Obriši')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Obriši profakturu')
                        ->modalDescription('Da li ste sigurni da želite da obrišete ovu profakturu? Ova akcija se ne može poništiti.')
                        ->modalSubmitActionLabel(__('actions.delete'))
                        ->action(function ($record) {
                            $record->delete();

                            Notification::make()
                                ->title('Profaktura obrisana')
                                ->body("Profaktura broj {$record->invoice_number} je uspešno obrisana.")
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
                        ->modalHeading('Označi profakture kao plaćene')
                        ->modalDescription('Da li sigurno želite da označite odabrane profakture kao plaćene?')
                        ->modalSubmitActionLabel(__('actions.mark_as_paid'))
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
                                ->title('Profakture označene kao plaćene')
                                ->body("Uspešno je označeno {$count} profaktura/e kao plaćeno.")
                                ->success()
                                ->send();
                        }),

                    DeleteBulkAction::make()
                        ->label('Obriši')
                        ->requiresConfirmation()
                        ->modalHeading('Obriši profakture')
                        ->modalDescription('Da li ste sigurni da želite da obrišete odabrane profakture? Ova akcija se ne može poništiti.')
                        ->modalSubmitActionLabel(__('actions.delete'))
                        ->deselectRecordsAfterCompletion()
                        ->successNotification(
                            Notification::make()
                                ->title('Profakture obrisane')
                                ->body('Odabrane profakture su uspešno obrisane.')
                                ->success()
                        ),
                ]),
            ]);
    }
}
