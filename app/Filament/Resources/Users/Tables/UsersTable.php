<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Ime')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('company_name')
                    ->label('Kompanija')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('subscription_status')
                    ->label('Pretplata')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        if ($record->is_grandfathered) {
                            return 'Grandfather';
                        }

                        if ($record->subscribed('default')) {
                            $subscription = $record->subscription('default');

                            return $subscription->onTrial() ? 'Trial' : 'Basic';
                        }

                        return 'Free';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Grandfather' => 'warning',
                        'Basic' => 'success',
                        'Trial' => 'info',
                        'Free' => 'gray',
                    }),

                TextColumn::make('monthly_invoices')
                    ->label('Fakture (mesec)')
                    ->getStateUsing(function ($record) {
                        $current = $record->getMonthlyInvoiceCount();
                        $limit = $record->getMonthlyInvoiceLimit();

                        if ($limit === PHP_INT_MAX) {
                            return "{$current} / ∞";
                        }

                        return "{$current} / {$limit}";
                    })
                    ->badge()
                    ->color(function ($record) {
                        if ($record->getMonthlyInvoiceLimit() === PHP_INT_MAX) {
                            return 'success';
                        }

                        $percentage = ($record->getMonthlyInvoiceCount() / $record->getMonthlyInvoiceLimit()) * 100;

                        return match (true) {
                            $percentage >= 100 => 'danger',
                            $percentage >= 66 => 'warning',
                            default => 'success',
                        };
                    }),

                IconColumn::make('is_grandfathered')
                    ->label('Grandfather')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-x-mark')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Kreiran')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('stripe_id')
                    ->label('Stripe ID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('free_plan')
                    ->label('Free Plan')
                    ->query(fn (Builder $query): Builder => $query->where('is_grandfathered', false)
                        ->whereDoesntHave('subscriptions')),

                Filter::make('subscribed')
                    ->label('Pretplaćeni')
                    ->query(fn (Builder $query): Builder => $query->whereHas('subscriptions')),

                Filter::make('grandfathered')
                    ->label('Grandfather')
                    ->query(fn (Builder $query): Builder => $query->where('is_grandfathered', true)),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Uredi'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
