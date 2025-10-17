<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class Register extends BaseRegister
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getSubscriptionInfoSection(),
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }

    protected function getSubscriptionInfoSection(): Component
    {
        return Section::make('Dobrodošli u Pausalci!')
            ->description('Registracijom dobijate pristup Free planu')
            ->schema([
                Grid::make()
                    ->schema([
                        Section::make('Free Plan')
                            ->description('Automatski aktiviran nakon registracije')
                            ->icon('heroicon-o-gift')
                            ->iconColor('success')
                            ->schema([
                                Placeholder::make('free_features')
                                    ->hiddenLabel()
                                    ->content(view('filament.components.plan-features', [
                                        'features' => [
                                            '3 fakture mesečno',
                                            'Osnovna fakturisanja',
                                            'Evidencija klijenata',
                                            'PDF izvoz',
                                        ],
                                    ])),
                            ])
                            ->collapsible()
                            ->collapsed(false),

                        Section::make('Basic Plan')
                            ->description('Nadogradite kasnije za neograničene fakture')
                            ->icon('heroicon-o-star')
                            ->iconColor('primary')
                            ->schema([
                                Placeholder::make('basic_features')
                                    ->hiddenLabel()
                                    ->content(view('filament.components.plan-features', [
                                        'features' => [
                                            'Neograničen broj faktura',
                                            'Sva osnovna fakturisanja',
                                            'Evidencija klijenata',
                                            'PDF izvoz',
                                            'Email podrška',
                                            '7 dana besplatno probnog perioda',
                                        ],
                                        'price' => '600 RSD/mesec',
                                        'yearly_price' => '6000 RSD/godinu (2 meseca gratis)',
                                    ])),
                            ])
                            ->collapsible()
                            ->collapsed(true),
                    ])
                    ->columns(2),
            ])
            ->columnSpanFull();
    }
}
