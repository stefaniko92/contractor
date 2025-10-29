<?php

namespace App\Filament\Resources\Profakturas\Tables;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

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
                    ->sortable(),

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
                //
            ])
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
                    ->modalSubmitActionLabel('Kreiraj fakturu')
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
                        ->modalSubmitActionLabel('Kreiraj avansnu fakturu')
                        ->modalIcon('heroicon-o-document-text')
                        ->visible(function ($record) {
                            // Only show for non-storno profakturas
                            return ! $record->is_storno;
                        })
                        ->action(function ($record) {
                            // TODO: Implement avans invoice creation
                            Notification::make()
                                ->title('Funkcija u izradi')
                                ->body('Kreiranje avansne fakture će uskoro biti dostupno.')
                                ->warning()
                                ->send();
                        }),

                    Action::make('send')
                        ->label('Pošalji')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('info')
                        ->action(function () {
                            // TODO: Implement send functionality
                        }),

                    Action::make('delete')
                        ->label('Obriši')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Obriši profakturu')
                        ->modalDescription('Da li ste sigurni da želite da obrišete ovu profakturu? Ova akcija se ne može poništiti.')
                        ->modalSubmitActionLabel('Obriši')
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
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
