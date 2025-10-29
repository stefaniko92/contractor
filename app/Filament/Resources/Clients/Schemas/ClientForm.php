<?php

namespace App\Filament\Resources\Clients\Schemas;

use App\Services\PibLookupService;
use Filament\Actions\Action as FormAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Hidden::make('user_id')
                            ->default(Auth::id()),

                        Section::make('Tip klijenta')
                            ->schema([
                                Radio::make('client_type')
                                    ->label('Tip klijenta')
                                    ->options([
                                        'pravno_lice' => 'Pravno lice',
                                        'fizicko_lice' => 'Fizičko lice',
                                        'javno_preduzece' => 'Javno preduzeće',
                                    ])
                                    ->default('pravno_lice')
                                    ->required()
                                    ->live(),

                                Radio::make('is_domestic')
                                    ->label('Lokacija klijenta')
                                    ->options([
                                        1 => 'Domaći klijent',
                                        0 => 'Strani klijent',
                                    ])
                                    ->default(1)
                                    ->required()
                                    ->live()
                                    ->inline(),
                            ])
                            ->columnSpanFull(),

                        Section::make('Osnovne informacije')
                            ->schema([
                                TextInput::make('company_name')
                                    ->label('Naziv kompanije')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('tax_id')
                                    ->label('PIB')
                                    ->maxLength(255)
                                    ->live(onBlur: true),

                                FormAction::make('fetch_by_pib')
                                    ->label('Pretraži po PIB-u')
                                    ->icon('heroicon-o-magnifying-glass')
                                    ->color('primary')
                                    ->action(function (Set $set, Get $get) {
                                        $pib = $get('tax_id');

                                        if (empty($pib)) {
                                            Notification::make()
                                                ->title('PIB je obavezan')
                                                ->body('Molimo unesite PIB pre preuzimanja podataka.')
                                                ->warning()
                                                ->send();

                                            return;
                                        }

                                        $pibLookupService = new PibLookupService;
                                        $result = $pibLookupService->fetchByPib($pib);

                                        if (! $result['success']) {
                                            Notification::make()
                                                ->title('Greška pri preuzimanju podataka')
                                                ->body($result['error'] ?? 'Podaci nisu pronađeni.')
                                                ->danger()
                                                ->send();

                                            return;
                                        }

                                        // Transform and fill the form
                                        $clientData = $pibLookupService->transformToClientData($result);

                                        foreach ($clientData as $field => $value) {
                                            if ($value !== null) {
                                                $set($field, $value);
                                            }
                                        }

                                        Notification::make()
                                            ->title('Podaci uspešno preuzeti')
                                            ->body('Informacije o kompaniji su automatski popunjene.')
                                            ->success()
                                            ->send();
                                    }),

                                TextInput::make('registration_number')
                                    ->label('Matični broj')
                                    ->maxLength(255),

                                TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->maxLength(255),

                                TextInput::make('phone')
                                    ->label('Telefon')
                                    ->tel()
                                    ->maxLength(255),

                                TextInput::make('default_place_of_sale')
                                    ->label('Uobičajeno mesto prometa')
                                    ->maxLength(255)
                                    ->default('Beograd'),

                                Select::make('currency')
                                    ->label('Podrazumevana valuta')
                                    ->options([
                                        'RSD' => 'RSD - Srpski dinar',
                                        'EUR' => 'EUR - Evro',
                                        'USD' => 'USD - Američki dolar',
                                        'GBP' => 'GBP - Britanska funta',
                                        'CHF' => 'CHF - Švajcarski franak',
                                    ])
                                    ->default('RSD')
                                    ->helperText('Ova valuta će biti automatski izabrana pri kreiranju fakture')
                                    ->required(),

                                Textarea::make('address')
                                    ->label('Adresa')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),

                        Section::make('Informacije za strane klijente')
                            ->schema([
                                TextInput::make('city')
                                    ->label('Grad')
                                    ->maxLength(255),

                                TextInput::make('country')
                                    ->label('Zemlja')
                                    ->maxLength(255),

                                TextInput::make('vat_number')
                                    ->label('VAT/EIB broj')
                                    ->maxLength(255),
                            ])
                            ->columns(2)
                            ->columnSpanFull()
                            ->visible(fn ($get) => ! $get('is_domestic')),
                        Section::make('Dodatne informacije')
                            ->schema([
                                Textarea::make('notes')
                                    ->label('Napomene')
                                    ->rows(4)
                                    ->columnSpanFull(),
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ])
            ->columns(1);
    }
}
