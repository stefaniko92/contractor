<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

class MyCompanyForm extends Widget implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    protected string $view = 'filament.widgets.my-company-form';

    protected function getFormSchema(): array
    {
        return [
            Tabs::make('MyCompanyTabs')
                ->tabs([
                    Tab::make('Company')
                        ->schema([
                            TextInput::make('company_name')->label('Company Name')->required(),
                            TextInput::make('company_tax_id')->label('Tax ID'),
                            DatePicker::make('company_registration_date')->label('Registration Date'),
                            Textarea::make('company_address')->label('Address'),
                        ]),
                    Tab::make('Owner')
                        ->schema([
                            TextInput::make('owner_name')->label('Owner Name')->required(),
                            Select::make('owner_gender')->label('Gender')->options([
                                'male' => 'Male',
                                'female' => 'Female',
                                'other' => 'Other',
                            ]),
                            TextInput::make('owner_email')->label('Email')->email(),
                        ]),
                ])
        ];
    }
}
