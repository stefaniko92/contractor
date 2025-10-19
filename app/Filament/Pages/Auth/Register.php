<?php

namespace App\Filament\Pages\Auth;

use Filament\Actions\Action;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;

class Register extends BaseRegister
{
    protected Width|string|null $maxWidth = Width::SevenExtraLarge;

    public function getHeading(): string
    {
        return 'Registracija';
    }

    public function getSubheading(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        if (! filament()->hasLogin()) {
            return null;
        }

        return new \Illuminate\Support\HtmlString('ili '.$this->loginAction->toHtml());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make('Lični podaci')
                        ->description('Unesite vaše osnovne informacije')
                        ->schema([
                            $this->getNameFormComponent(),
                            $this->getEmailFormComponent(),
                            Select::make('citizenship')
                                ->label('Državljanstvo')
                                ->options([
                                    'Srbija' => 'Srbija',
                                    'Bosna i Hercegovina' => 'Bosna i Hercegovina',
                                    'Hrvatska' => 'Hrvatska',
                                    'Crna Gora' => 'Crna Gora',
                                    'Slovenija' => 'Slovenija',
                                    'Severna Makedonija' => 'Severna Makedonija',
                                    'Ostalo' => 'Ostalo',
                                ])
                                ->required()
                                ->native(false)
                                ->searchable(),
                            Select::make('language')
                                ->label('Jezik aplikacije')
                                ->options([
                                    'sr' => 'Srpski',
                                    'en' => 'English',
                                ])
                                ->default('sr')
                                ->required()
                                ->native(false),
                            $this->getPasswordFormComponent(),
                            $this->getPasswordConfirmationFormComponent(),
                        ])
                        ->columns(2),
                    Step::make('Izaberite plan')
                        ->description('Informacije o dostupnim planovima')
                        ->schema([
                            $this->getSubscriptionInfoSection(),
                        ]),
                ])
                    ->nextAction(fn ($action) => $action->label('Dalje'))
                    ->previousAction(fn ($action) => $action->label('Nazad'))
                    ->submitAction(new \Illuminate\Support\HtmlString(view('filament.pages.auth.wizard-submit-button')->render()))
                    ->columnSpanFull(),
            ]);
    }

    protected function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label('Ime i prezime')
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Email adresa')
            ->email()
            ->required()
            ->maxLength(255)
            ->unique($this->getUserModel());
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('Lozinka')
            ->password()
            ->required()
            ->revealable()
            ->dehydrated(fn ($state) => filled($state))
            ->same('passwordConfirmation');
    }

    protected function getPasswordConfirmationFormComponent(): Component
    {
        return TextInput::make('passwordConfirmation')
            ->label('Potvrdite lozinku')
            ->password()
            ->required()
            ->revealable()
            ->dehydrated(false);
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
                                        'buttonLabel' => 'Započni besplatno',
                                        'buttonColor' => 'success',
                                        'outlined' => false,
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
                                        'buttonLabel' => 'Izaberi Basic',
                                        'buttonColor' => 'primary',
                                        'outlined' => true,
                                    ])),
                            ])
                            ->collapsible()
                            ->collapsed(false),

                        Section::make('Premium Plan')
                            ->description('Sve iz Basic plana + napredne funkcije')
                            ->icon('heroicon-o-sparkles')
                            ->iconColor('warning')
                            ->schema([
                                Placeholder::make('premium_features')
                                    ->hiddenLabel()
                                    ->content(view('filament.components.plan-features', [
                                        'features' => [
                                            'Sve iz Basic plana',
                                            'Automatsko slanje faktura',
                                            'Prilagođeni PDF šabloni',
                                            'Napredna analitika',
                                            'Prioritetna podrška',
                                            'Multi-valuta podrška',
                                        ],
                                        'price' => '1200 RSD/mesec',
                                        'yearly_price' => '12000 RSD/godinu (2 meseca gratis)',
                                        'buttonLabel' => 'Izaberi Premium',
                                        'buttonColor' => 'warning',
                                        'outlined' => true,
                                    ])),
                            ])
                            ->collapsible()
                            ->collapsed(false),
                    ])
                    ->columns(3),
            ])
            ->columnSpanFull();
    }

    protected function getFormActions(): array
    {
        return [];
    }

    public function loginAction(): Action
    {
        return parent::loginAction()->label('prijavite se na vaš nalog');
    }
}
