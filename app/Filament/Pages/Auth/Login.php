<?php

namespace App\Filament\Pages\Auth;

use Filament\Actions\Action;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;

class Login extends BaseLogin
{
    public function getHeading(): string
    {
        return 'Prijavite se';
    }

    public function getSubheading(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        if (! filament()->hasRegistration()) {
            return null;
        }

        return new \Illuminate\Support\HtmlString('ili '.$this->registerAction->toHtml());
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Email adresa')
            ->email()
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('Lozinka')
            ->hint(filament()->hasPasswordReset() ? new \Illuminate\Support\HtmlString('<a href="'.filament()->getRequestPasswordResetUrl().'" class="text-sm font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300">Zaboravili ste lozinku?</a>') : null)
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->autocomplete('current-password')
            ->required()
            ->extraInputAttributes(['tabindex' => 2]);
    }

    protected function getRememberFormComponent(): Component
    {
        return Checkbox::make('remember')
            ->label('Zapamti me');
    }

    protected function getAuthenticateFormAction(): Action
    {
        return Action::make('authenticate')
            ->label('Prijavite se')
            ->submit('authenticate');
    }

    public function registerAction(): Action
    {
        return parent::registerAction()->label('registrujte se za nalog');
    }

    public function getFooter(): ?string
    {
        return view('filament.pages.auth.login-footer')->render();
    }
}
