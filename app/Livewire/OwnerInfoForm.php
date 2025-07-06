<?php

namespace App\Livewire;

use Filament\Widgets\Widget;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use App\Models\UserCompany;
use App\Models\CompanyOwner;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class OwnerInfoForm extends Widget implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    protected string $view = 'livewire.owner-info-form';

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('first_name')->label('First Name')->required(),
            TextInput::make('last_name')->label('Last Name')->required(),
            TextInput::make('parent_name')->label('Parent Name'),
            TextInput::make('nationality')->label('Nationality'),
            TextInput::make('personal_id_number')->label('Personal ID Number'),
            TextInput::make('education_level')->label('Education Level'),
            Select::make('gender')->label('Gender')->options([
                'male' => 'Male',
                'female' => 'Female',
                'other' => 'Other',
            ]),
            TextInput::make('city')->label('City'),
            TextInput::make('municipality')->label('Municipality'),
            Textarea::make('address')->label('Address'),
            TextInput::make('address_number')->label('Address Number'),
            TextInput::make('email')->label('Email')->email(),
        ];
    }

    public function save()
    {
        $validated = $this->form->getState();

        $userCompany = UserCompany::where('user_id', Auth::id())->first();

        if ($userCompany) {
            CompanyOwner::updateOrCreate(
                ['user_company_id' => $userCompany->id],
                $validated
            );

            Notification::make()
                ->title('Owner info saved!')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Please fill company info first.')
                ->danger()
                ->send();
        }
    }
}
