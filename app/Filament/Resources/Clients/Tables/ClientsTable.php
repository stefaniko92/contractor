<?php

namespace App\Filament\Resources\Clients\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ClientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->where('user_id', Auth::id()))
            ->columns([
                TextColumn::make('company_name')
                    ->label('Naziv kompanije')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tax_id')
                    ->label('PIB')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('address')
                    ->label('Adresa')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),
                TextColumn::make('city')
                    ->label('Grad')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('country')
                    ->label('Zemlja')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('client_type')
                    ->label('Tip klijenta')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pravno_lice' => 'success',
                        'fizicko_lice' => 'info',
                        'javno_preduzece' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pravno_lice' => 'Pravno lice',
                        'fizicko_lice' => 'Fizičko lice',
                        'javno_preduzece' => 'Javno preduzeće',
                        default => $state,
                    })
                    ->toggleable(),
                TextColumn::make('is_domestic')
                    ->label('Lokacija')
                    ->badge()
                    ->color(fn ($state): string => $state ? 'success' : 'info')
                    ->formatStateUsing(fn ($state): string => $state ? 'Domaći' : 'Strani')
                    ->toggleable(),
                TextColumn::make('currency')
                    ->label('Valuta')
                    ->badge()
                    ->color('gray')
                    ->toggleable()
                    ->visible(fn ($record): bool => $record && $record->is_domestic == 0),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
                TextColumn::make('phone')
                    ->label('Telefon')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
                TextColumn::make('registration_number')
                    ->label('Matični broj')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('vat_number')
                    ->label('VAT/EIB')
                    ->searchable()
                    ->toggleable()
                    ->visible(fn ($record): bool => $record && $record->is_domestic == 0),
                TextColumn::make('default_place_of_sale')
                    ->label('Mesto prometa')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('notes')
                    ->label('Napomene')
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
                    ->label('Kreiran')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Ažuriran')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Uredi'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Obriši odabrane'),
                ]),
            ])
            ->emptyStateHeading('Nema klijenata')
            ->emptyStateDescription('Dodajte prvog klijenta da biste počeli sa kreiranjem faktura.')
            ->defaultSort('created_at', 'desc');
    }
}
