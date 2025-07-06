<?php

namespace App\Filament\Resources\CompanyOwners\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CompanyOwnersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(fn (Builder $query) => $query->whereHas('userCompany', fn (Builder $q) => $q->where('user_id', Auth::id())))
            ->columns([
                TextColumn::make('userCompany.company_name')
                    ->label('Kompanija')
                    ->sortable(),
                    
                TextColumn::make('first_name')
                    ->label('Ime')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('last_name')
                    ->label('Prezime')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('personal_id_number')
                    ->label('JMBG')
                    ->searchable()
                    ->copyable(),
                    
                TextColumn::make('nationality')
                    ->label('Nacionalnost')
                    ->searchable(),
                    
                TextColumn::make('gender')
                    ->label('Pol')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'male' => 'Muški',
                        'female' => 'Ženski',
                        'other' => 'Ostalo',
                        default => $state,
                    }),
                    
                TextColumn::make('city')
                    ->label('Grad')
                    ->searchable(),
                    
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),
                    
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
            ]);
    }
}
