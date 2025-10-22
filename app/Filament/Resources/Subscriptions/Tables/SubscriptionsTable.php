<?php

namespace App\Filament\Resources\Subscriptions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SubscriptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Korisnik')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Tip pretplate')
                    ->searchable(),
                TextColumn::make('stripe_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'past_due' => 'warning',
                        'canceled' => 'danger',
                        'incomplete' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('trial_ends_at')
                    ->label('Trial završen')
                    ->dateTime('d.m.Y H:i')
                    ->nullable(),
                TextColumn::make('ends_at')
                    ->label('Pretplata završena')
                    ->dateTime('d.m.Y H:i')
                    ->nullable(),
                TextColumn::make('created_at')
                    ->label('Početak pretplate')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('stripe_status')
                    ->label('Status pretplate')
                    ->options([
                        'active' => 'Aktivna',
                        'past_due' => 'Prekoračena',
                        'canceled' => 'Otkazana',
                        'incomplete' => 'Nepotpuna',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
