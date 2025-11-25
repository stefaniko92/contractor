<?php

namespace App\Filament\Resources\KpoEntries\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class KpoEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('entry_number')
                    ->label('R.Br.')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('date')
                    ->label('Datum')
                    ->date('d.m.Y')
                    ->sortable(),

                TextColumn::make('invoice_mark')
                    ->label('Oznaka Računa')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('client_name')
                    ->label('Klijent')
                    ->searchable()
                    ->weight(FontWeight::Bold)
                    ->limit(30),

                TextColumn::make('client.company_name')
                    ->label('Povezani Klijent')
                    ->toggleable()
                    ->searchable()
                    ->limit(25)
                    ->placeholder('Nije povezan'),

                TextColumn::make('product_service_description')
                    ->label('Opis')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('income_amount')
                    ->label('Prihod')
                    ->money('RSD')
                    ->alignEnd()
                    ->color('success'),

                TextColumn::make('expense_amount')
                    ->label('Rashod')
                    ->money('RSD')
                    ->alignEnd()
                    ->color('danger'),

                TextColumn::make('kpoUpload.file_name')
                    ->label('KPO Fajl')
                    ->limit(20)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Kreirano')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('kpo_upload_id')
                    ->label('KPO Upload')
                    ->relationship('kpoUpload', 'file_name')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                SelectFilter::make('client_id')
                    ->label('Klijent')
                    ->relationship('client', 'company_name')
                    ->searchable()
                    ->preload()
                    ->multiple(),
            ])
            ->filtersFormColumns(2)
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'desc');
    }
}
