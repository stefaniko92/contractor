<?php

namespace App\Livewire;

use App\Models\UserCompany;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class CompanyInfoForm extends Widget implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    protected string $view = 'livewire.company-info-form';

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('company_name')->label('Company Name')->required(),
            TextInput::make('company_full_name')->label('Full Company Name'),
            TextInput::make('company_tax_id')->label('Tax ID'),
            TextInput::make('company_registry_number')->label('Registry Number'),
            TextInput::make('company_activity_code')->label('Activity Code'),
            Textarea::make('company_activity_desc')->label('Activity Description'),
            DatePicker::make('company_registration_date')->label('Registration Date'),
            TextInput::make('company_city')->label('City'),
            TextInput::make('company_postal_code')->label('Postal Code'),
            TextInput::make('company_status')->label('Status'),
            TextInput::make('company_municipality')->label('Municipality'),
            Textarea::make('company_address')->label('Address'),
            TextInput::make('company_address_number')->label('Address Number'),
            TextInput::make('company_phone')->label('Phone'),
            TextInput::make('company_email')->label('Email')->email(),
            Toggle::make('show_email_on_invoice')->label('Show Email on Invoice'),
            TextInput::make('company_foreign_account_number')->label('Foreign Account Number'),
            TextInput::make('company_foreign_account_bank')->label('Foreign Account Bank'),
            FileUpload::make('company_logo_path')->label('Company Logo')->image(),
        ];
    }

    public function save()
    {
        $validated = $this->form->getState();

        UserCompany::updateOrCreate(
            ['user_id' => Auth::id()],
            $validated
        );

        Notification::make()
            ->title('Company info saved!')
            ->success()
            ->send();
    }
}
