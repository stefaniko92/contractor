<?php

namespace App\Filament\Pages;

use App\Models\UserCompany;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\Auth;

class CompanyInfo extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $title = 'Podaci o kompaniji';

    protected static ?int $navigationSort = 11;

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.moja_kompanija');
    }

    public static function getNavigationLabel(): string
    {
        return __('navigation.menu_items.company_info');
    }

    public function getTitle(): string
    {
        return __('company.page_title');
    }

    protected string $view = 'filament.pages.company-info';

    public ?array $data = [];

    public function mount()
    {
        $userCompany = \App\Models\UserCompany::where('user_id', Auth::id())->first();

        if ($userCompany) {
            $this->data = $userCompany->toArray();

            // Convert company_logo_path string to array for FileUpload component
            if (! empty($this->data['company_logo_path']) && is_string($this->data['company_logo_path'])) {
                $this->data['company_logo_path'] = [$this->data['company_logo_path']];
            }

            // Convert date to format DatePicker expects (Y-m-d)
            if (! empty($this->data['company_registration_date'])) {
                $date = \Carbon\Carbon::parse($this->data['company_registration_date']);
                $this->data['company_registration_date'] = $date->format('Y-m-d');
            }
        } else {
            $this->data = [];
        }
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make(__('company.sections.company_info'))
                ->schema([
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
                    FileUpload::make('company_logo_path')
                        ->label(__('company.fields.company_logo_path'))
                        ->image()
                        ->disk('s3')
                        ->visibility('private'),
                ])
                ->columns(2),

            Section::make(__('company.sections.invoice_settings'))
                ->schema([
                    Textarea::make('invoice_note_domestic')
                        ->label(__('company.fields.invoice_note_domestic'))
                        ->rows(3)
                        ->helperText('Napomena će biti prikazana na domaćim fakturama'),
                    Textarea::make('invoice_note_foreign')
                        ->label(__('company.fields.invoice_note_foreign'))
                        ->rows(3)
                        ->helperText('Napomena će biti prikazana na inostranim fakturama'),
                ])
                ->columns(2)
                ->collapsible(),
        ];
    }

    protected function getFormStatePath(): string
    {
        return 'data';
    }

    public function save()
    {
        $validated = $this->form->getState();

        // Convert company_logo_path array back to string for database storage
        if (! empty($validated['company_logo_path']) && is_array($validated['company_logo_path'])) {
            $validated['company_logo_path'] = $validated['company_logo_path'][0] ?? null;
        }

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
