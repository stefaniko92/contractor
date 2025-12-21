<?php

namespace App\Filament\Pages\Auth;

use Filament\Actions\Action;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;
use Laravel\Cashier\Subscription;

class Register extends BaseRegister
{
    protected Width|string|null $maxWidth = Width::SevenExtraLarge;

    public ?string $selectedPlan = 'free';

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
                        ->description('Kliknite na plan koji želite')
                        ->schema([
                            \Filament\Forms\Components\ViewField::make('plan_cards')
                                ->view('filament.pages.auth.plan-selection')
                                ->dehydrated(false)
                                ->columnSpanFull(),
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

    protected function getFormActions(): array
    {
        return [];
    }

    public function loginAction(): Action
    {
        return parent::loginAction()->label('prijavite se na vaš nalog');
    }

    protected function handleRegistration(array $data): Model
    {
        $user = parent::handleRegistration($data);

        if ($this->selectedPlan === 'free' || ! $this->selectedPlan) {
            // Create a free "subscription" record for tracking
            // This won't use Stripe, just a local database record
            Subscription::create([
                'user_id' => $user->id,
                'name' => 'default',
                'stripe_id' => 'free_plan_'.time(),
                'stripe_status' => 'active',
                'stripe_price' => null,
                'quantity' => 1,
                'trial_ends_at' => null,
                'ends_at' => null,
            ]);
        } else {
            // Paid plan selected - store in session and redirect to Stripe
            session()->put('selected_plan_after_registration', $this->selectedPlan);
            redirect()->setIntendedUrl(route('filament.admin.pages.subscription-management'));
        }

        return $user;
    }
}
