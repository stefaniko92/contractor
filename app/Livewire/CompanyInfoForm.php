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
            TextInput::make('company_name')->label(__('company.fields.company_name'))->required(),
            TextInput::make('company_full_name')->label(__('company.fields.company_full_name')),
            TextInput::make('company_tax_id')->label(__('company.fields.company_tax_id')),
            TextInput::make('company_registry_number')->label(__('company.fields.company_registry_number')),
            TextInput::make('company_activity_code')->label(__('company.fields.company_activity_code')),
            Textarea::make('company_activity_desc')->label(__('company.fields.company_activity_desc')),
            DatePicker::make('company_registration_date')->label(__('company.fields.company_registration_date')),
            TextInput::make('company_city')->label(__('company.fields.company_city')),
            TextInput::make('company_postal_code')->label(__('company.fields.company_postal_code')),
            TextInput::make('company_status')->label(__('company.fields.company_status')),
            TextInput::make('company_municipality')->label(__('company.fields.company_municipality')),
            Textarea::make('company_address')->label(__('company.fields.company_address')),
            TextInput::make('company_address_number')->label(__('company.fields.company_address_number')),
            TextInput::make('company_phone')->label(__('company.fields.company_phone')),
            TextInput::make('company_email')->label(__('company.fields.company_email'))->email(),
            Toggle::make('show_email_on_invoice')->label(__('company.fields.show_email_on_invoice')),
            TextInput::make('company_foreign_account_number')->label(__('company.fields.company_foreign_account_number')),
            TextInput::make('company_foreign_account_bank')->label(__('company.fields.company_foreign_account_bank')),
            FileUpload::make('company_logo_path')
                ->label(__('company.fields.company_logo_path'))
                ->image()
                ->disk('s3')
                ->visibility('private'),
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
            ->title(__('company.notifications.saved'))
            ->success()
            ->send();
    }
}
