<?php

namespace App\Livewire;

use App\Models\CompanyOwner;
use App\Models\UserCompany;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class OwnerInfoForm extends Widget implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    protected string $view = 'livewire.owner-info-form';

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('first_name')->label(__('owner.fields.first_name'))->required(),
            TextInput::make('last_name')->label(__('owner.fields.last_name'))->required(),
            TextInput::make('parent_name')->label(__('owner.fields.parent_name')),
            TextInput::make('nationality')->label(__('owner.fields.nationality')),
            TextInput::make('personal_id_number')->label(__('owner.fields.personal_id_number')),
            TextInput::make('education_level')->label(__('owner.fields.education_level')),
            Select::make('gender')->label(__('owner.fields.gender'))->options([
                'male' => __('owner.gender_options.male'),
                'female' => __('owner.gender_options.female'),
                'other' => __('owner.gender_options.other'),
            ]),
            TextInput::make('city')->label(__('owner.fields.city')),
            TextInput::make('municipality')->label(__('owner.fields.municipality')),
            Textarea::make('address')->label(__('owner.fields.address')),
            TextInput::make('address_number')->label(__('owner.fields.address_number')),
            TextInput::make('email')->label(__('owner.fields.email'))->email(),
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
                ->title(__('owner.notifications.saved'))
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title(__('owner.notifications.company_required'))
                ->danger()
                ->send();
        }
    }
}
