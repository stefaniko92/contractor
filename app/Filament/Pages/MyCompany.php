<?php

namespace App\Filament\Pages;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Actions\Action;
use BackedEnum;

class MyCompany extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $title = 'My Company';
    protected static ?string $navigationLabel = 'My Company';
    protected static ?string $slug = 'my-company';

    public ?array $data = [];

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('company_name')->label('Company Name')->required(),
            TextInput::make('company_tax_id')->label('Tax ID'),
            DatePicker::make('company_registration_date')->label('Registration Date'),
            Textarea::make('company_address')->label('Address'),
            TextInput::make('owner_name')->label('Owner Name')->required(),
            Select::make('owner_gender')->label('Gender')->options([
                'male' => 'Male',
                'female' => 'Female',
                'other' => 'Other',
            ]),
            TextInput::make('owner_email')->label('Email')->email(),
        ];
    }

    public function save()
    {
        // Placeholder for save logic
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save')
                ->submit('save')
                ->color('primary'),
        ];
    }

    protected string $view = 'filament.pages.my-company';
}
