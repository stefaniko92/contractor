<?php

namespace App\Filament\Resources\AvansnaFakturas\Tables;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AvansnaFakturasTable
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
                    ->label('Broj avansne fakture')
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
                    ->label('Tip avansne fakture')
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
                Action::make('print')
                    ->label('Štampaj')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->url(fn ($record): string => route('invoices.print', $record))
                    ->openUrlInNewTab(),

                Action::make('delete')
                    ->label('Obriši')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Obriši avansnu fakturu')
                    ->modalDescription('Da li ste sigurni da želite da obrišete ovu avansnu fakturu? Ova akcija se ne može poništiti.')
                    ->modalSubmitActionLabel('Obriši')
                    ->action(function ($record) {
                        $record->delete();

                        Notification::make()
                            ->title('Avansna faktura obrisana')
                            ->body("Avansna faktura broj {$record->invoice_number} je uspešno obrisana.")
                            ->success()
                            ->send();
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

                    Action::make('storno')
                        ->label('Storniraj')
                        ->icon('heroicon-o-x-mark')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Storniraj avansnu fakturu')
                        ->modalDescription(function ($record) {
                            return "Da li ste sigurni da želite da stornirate avansnu fakturu {$record->invoice_number}? Biće kreirana nova storno avansna faktura sa negativnim iznosima u skladu sa srpskim zakonskim propisima. Obe avansne fakture će biti zabeležene u knjigi prihoda.";
                        })
                        ->modalSubmitActionLabel('Storniraj')
                        ->modalIcon('heroicon-o-exclamation-triangle')
                        ->visible(function ($record) {
                            // Only show storno action for issued avansna fakturas that are not storno invoices themselves and don't already have a storno
                            return ! $record->is_storno && $record->status !== 'in_preparation' && $record->stornoInvoices()->count() === 0;
                        })
                        ->action(function ($record) {
                            // Create storno (reversal) avansna faktura with negative amounts
                            $stornoInvoice = Invoice::create([
                                'user_id' => $record->user_id,
                                'client_id' => $record->client_id,
                                'invoice_type' => $record->invoice_type,
                                'invoice_document_type' => $record->invoice_document_type,
                                'issue_date' => now(),
                                'due_date' => now()->addDays(30),
                                'trading_place' => $record->trading_place,
                                'currency' => $record->currency,
                                'description' => 'Storno avansne fakture '.$record->invoice_number.' od '.$record->issue_date->format('d.m.Y'),
                                'status' => 'storned',
                                'amount' => -$record->amount, // Negative amount
                                'is_storno' => true,
                                'original_invoice_id' => $record->id,
                                'original_invoice_number' => $record->invoice_number,
                                'original_invoice_date' => $record->issue_date,
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

                            // Update original avansna faktura status to charged (since stornoing implies it was paid)
                            $record->update(['status' => 'charged']);

                            Notification::make()
                                ->title('Storno avansna faktura kreirana')
                                ->body("Kreirana je storno avansna faktura {$stornoInvoice->invoice_number} za originalnu avansnu fakturu {$record->invoice_number}. Obe avansne fakture su zabeležene u knjizi prihoda u skladu sa zakonskim propisima.")
                                ->success()
                                ->send();
                        }),

                    Action::make('send')
                        ->label('Pošalji')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('info')
                        ->action(function () {
                            // TODO: Implement send functionality
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
