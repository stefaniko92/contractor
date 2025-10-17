<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Osnovne informacije')
                    ->schema([
                        TextInput::make('name')
                            ->label('Ime')
                            ->required(),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required(),
                        DateTimePicker::make('email_verified_at')
                            ->label('Email verifikovan'),
                        TextInput::make('password')
                            ->label('Lozinka')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn ($context) => $context === 'create'),
                    ])
                    ->columns(2),

                Section::make('Informacije o kompaniji')
                    ->schema([
                        TextInput::make('company_name')
                            ->label('Naziv kompanije'),
                        TextInput::make('tax_id')
                            ->label('PIB'),
                        Textarea::make('address')
                            ->label('Adresa')
                            ->columnSpanFull(),
                        TextInput::make('phone')
                            ->label('Telefon')
                            ->tel(),
                        TextInput::make('default_currency')
                            ->label('Podrazumevana valuta')
                            ->default('RSD'),
                        TextInput::make('swift_code')
                            ->label('SWIFT kod'),
                        TextInput::make('iban')
                            ->label('IBAN'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Pretplata i naplata')
                    ->schema([
                        Toggle::make('is_grandfathered')
                            ->label('Grandfather (Besplatno zauvek)')
                            ->helperText('Omogućite ovo da korisniku date neograničen besplatan pristup zauvek')
                            ->inline(false),

                        Placeholder::make('subscription_status')
                            ->label('Status pretplate')
                            ->content(function ($record) {
                                if (! $record) {
                                    return 'Novi korisnik - još nema pretplatu';
                                }

                                if ($record->is_grandfathered) {
                                    return '✓ Grandfather (Besplatno zauvek)';
                                }

                                if ($record->subscribed('default')) {
                                    $subscription = $record->subscription('default');
                                    $status = $subscription->onTrial() ? 'Na probnom periodu' : 'Aktivna';

                                    return "✓ {$status} - {$subscription->name}";
                                }

                                return 'Free Plan (3 fakture/mesec)';
                            }),

                        Placeholder::make('invoice_usage')
                            ->label('Upotreba faktura ovog meseca')
                            ->content(function ($record) {
                                if (! $record) {
                                    return '-';
                                }

                                $current = $record->getMonthlyInvoiceCount();
                                $limit = $record->getMonthlyInvoiceLimit();

                                if ($limit === PHP_INT_MAX) {
                                    return "{$current} / Neograničeno";
                                }

                                return "{$current} / {$limit}";
                            }),

                        TextInput::make('stripe_id')
                            ->label('Stripe Customer ID')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('pm_type')
                            ->label('Tip kartice')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('pm_last_four')
                            ->label('Poslednje 4 cifre kartice')
                            ->disabled()
                            ->dehydrated(false),

                        DateTimePicker::make('trial_ends_at')
                            ->label('Probni period ističe')
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }
}
