<?php

namespace App\Filament\Resources\UserCompanies\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class UserCompaniesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(fn (Builder $query) => $query->where('user_id', Auth::id()))
            ->columns([
                TextColumn::make('company_name')
                    ->label('Naziv kompanije')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('company_tax_id')
                    ->label('PIB')
                    ->searchable()
                    ->copyable(),
                    
                TextColumn::make('company_registry_number')
                    ->label('Matični broj')
                    ->searchable()
                    ->copyable(),
                    
                TextColumn::make('company_activity_code')
                    ->label('Šifra delatnosti')
                    ->searchable(),
                    
                TextColumn::make('company_city')
                    ->label('Grad')
                    ->searchable(),
                    
                TextColumn::make('company_phone')
                    ->label('Telefon')
                    ->searchable()
                    ->copyable(),
                    
                TextColumn::make('company_email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),
                    
                TextColumn::make('company_registration_date')
                    ->label('Datum registracije')
                    ->date('d.m.Y')
                    ->sortable(),
                    
                IconColumn::make('show_email_on_invoice')
                    ->label('Email na fakturi')
                    ->boolean(),
                    
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
