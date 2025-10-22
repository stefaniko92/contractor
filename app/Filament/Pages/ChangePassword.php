<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ChangePassword extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected string $view = 'filament.pages.change-password';

    protected static ?string $title = 'Promena Lozinke';

    protected static ?string $navigationLabel = 'Promena Lozinke';

    protected static string|\UnitEnum|null $navigationGroup = 'Postavke';

    protected static ?int $navigationSort = 30;

    public ?array $data = [];

    public function form(Schema $form): Schema
    {
        return $form
            ->schema($this->getFormSchema())
            ->statePath('data');
    }

    protected function getFormSchema(): array
    {
        return [
            SchemaComponents\Section::make('Promenite Vašu Lozinku')
                ->description('Unesite trenutnu lozinku i novu lozinku')
                ->schema([
                    Forms\Components\TextInput::make('current_password')
                        ->label('Trenutna Lozinka')
                        ->password()
                        ->revealable()
                        ->required()
                        ->columnSpanFull()
                        ->autocomplete('current-password'),
                    Forms\Components\TextInput::make('new_password')
                        ->label('Nova Lozinka')
                        ->password()
                        ->revealable()
                        ->required()
                        ->minLength(8)
                        ->same('new_password_confirmation')
                        ->autocomplete('new-password')
                        ->helperText('Lozinka mora imati najmanje 8 karaktera'),
                    Forms\Components\TextInput::make('new_password_confirmation')
                        ->label('Potvrdite Novu Lozinku')
                        ->password()
                        ->revealable()
                        ->required()
                        ->autocomplete('new-password'),
                ])
                ->columns(2),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $user = Auth::user();

        // Verify current password
        if (! Hash::check($data['current_password'], $user->password)) {
            Notification::make()
                ->danger()
                ->title('Greška')
                ->body('Trenutna lozinka nije tačna.')
                ->send();

            return;
        }

        // Update password
        $user->update([
            'password' => Hash::make($data['new_password']),
        ]);

        // Clear form
        $this->form->fill([]);

        Notification::make()
            ->success()
            ->title('Lozinka Promenjena')
            ->body('Vaša lozinka je uspešno promenjena.')
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('Promeni Lozinku')
                ->submit('save')
                ->color('primary'),
        ];
    }
}
