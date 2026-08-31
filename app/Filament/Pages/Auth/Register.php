<?php

namespace App\Filament\Pages\Auth;

use Filament\Actions\Action;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class Register extends BaseRegister
{
    protected Width|string|null $maxWidth = Width::SevenExtraLarge;

    public ?string $selectedPlan = 'free';

    public function getHeading(): string
    {
        return 'Registracija';
    }

    public function getSubheading(): string|Htmlable|null
    {
        if (! filament()->hasLogin()) {
            return null;
        }

        return new HtmlString('ili '.$this->loginAction->toHtml());
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
                            ViewField::make('plan_cards')
                                ->view('filament.pages.auth.plan-selection')
                                ->dehydrated(false)
                                ->columnSpanFull(),
                        ]),
                ])
                    ->nextAction(fn ($action) => $action->label(__('actions.next')))
                    ->previousAction(fn ($action) => $action->label(__('actions.back')))
                    ->submitAction(new HtmlString(view('filament.pages.auth.wizard-submit-button')->render()))
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

        if ($this->selectedPlan !== 'free' && $this->selectedPlan) {
            // Paid plan selected - store in session and redirect to Stripe
            session()->put('selected_plan_after_registration', $this->selectedPlan);
            redirect()->setIntendedUrl(route('filament.admin.pages.subscription-management'));
        }

        return $user;
    }
}
