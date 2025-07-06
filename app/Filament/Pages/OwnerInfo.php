<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use App\Models\UserCompany;
use App\Models\CompanyOwner;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class OwnerInfo extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\UnitEnum|null $navigationGroup = 'Moja kompanija';
    protected static ?string $navigationLabel = 'Podaci o vlasniku';
    protected static ?string $title = 'Podaci o vlasniku';
    protected string $view = 'filament.pages.owner-info';

    public ?array $data = [];

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

    protected function getFormStatePath(): string
    {
        return 'data';
    }

    public function mount()
    {
        $userCompany = \App\Models\UserCompany::where('user_id', Auth::id())->first();
        $owner = $userCompany?->companyOwner;
        $this->data = $owner ? $owner->toArray() : [];
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
