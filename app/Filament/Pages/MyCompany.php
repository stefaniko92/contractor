<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;

class MyCompany extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $title = 'My Company';

    protected static ?string $navigationLabel = 'My Company';

    protected static ?string $slug = 'my-company';

    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    protected function getFormSchema(): array
    {
        return [
            Section::make('Osnovne informacije kompanije')
                ->schema([
                    TextInput::make('company_name')->label('Naziv kompanije')->required(),
                    TextInput::make('company_tax_id')->label('PIB'),
                    DatePicker::make('company_registration_date')->label('Datum registracije'),
                    Textarea::make('company_address')->label('Adresa')->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('Bankovne informacije za inostrane fakture')
                ->schema([
                    TextInput::make('swift_code')
                        ->label('SWIFT kod')
                        ->helperText('Potrebno za inostrane fakture'),
                    TextInput::make('iban')
                        ->label('IBAN')
                        ->helperText('Potrebno za inostrane fakture'),
                ])
                ->columns(2),

            Section::make('Informacije o vlasniku')
                ->schema([
                    TextInput::make('owner_name')->label('Ime vlasnika')->required(),
                    Select::make('owner_gender')->label('Pol')->options([
                        'male' => 'Muški',
                        'female' => 'Ženski',
                        'other' => 'Ostalo',
                    ]),
                    TextInput::make('owner_email')->label('Email')->email(),
                ])
                ->columns(2),
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
