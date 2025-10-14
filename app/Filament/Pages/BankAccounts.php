<?php

namespace App\Filament\Pages;

use App\Models\Bank;
use App\Models\BankAccount;
use App\Models\UserCompany;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class BankAccounts extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $title = 'Bankovni računi';

    protected static ?int $navigationSort = 13;

    protected string $view = 'filament.pages.bank-accounts';

    public ?array $data = [];

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.moja_kompanija');
    }

    public static function getNavigationLabel(): string
    {
        return __('navigation.menu_items.bank_accounts');
    }

    public function getTitle(): string
    {
        return __('bank_accounts.page_title');
    }

    public function mount(): void
    {
        $userCompany = UserCompany::where('user_id', Auth::id())->first();

        if ($userCompany) {
            $this->data['bank_accounts'] = $userCompany->bankAccounts->toArray();
        } else {
            $this->data = ['bank_accounts' => []];
        }

        $this->form->fill($this->data);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Repeater::make('bank_accounts')
                    ->schema([
                        Select::make('account_type')
                            ->label(__('bank_accounts.fields.account_type'))
                            ->options([
                                'domestic' => __('bank_accounts.types.domestic'),
                                'foreign' => __('bank_accounts.types.foreign'),
                            ])
                            ->required()
                            ->default('domestic')
                            ->live()
                            ->afterStateUpdated(function ($get, callable $set, $state) {
                                if ($state === 'domestic') {
                                    $set('currency', 'RSD');
                                    $set('swift', null);
                                    $set('iban', null);
                                } else {
                                    $set('bank_id', null);
                                }
                            })
                            ->columnSpanFull(),

                        // Domestic bank selection
                        Select::make('bank_id')
                            ->label(__('bank_accounts.fields.bank'))
                            ->options(Bank::getActiveBanks())
                            ->searchable()
                            ->required(fn ($get) => $get('account_type') === 'domestic')
                            ->visible(fn ($get) => $get('account_type') === 'domestic')
                            ->live()
                            ->afterStateUpdated(function ($get, callable $set, $state) {
                                if ($state) {
                                    $bankData = Bank::getBankWithSwift($state);
                                    if ($bankData) {
                                        $set('bank_name', $bankData['name']);
                                        $set('swift', $bankData['swift']);
                                    }
                                }
                            }),

                        // Bank name (auto-filled for domestic, manual for foreign)
                        TextInput::make('bank_name')
                            ->label(__('bank_accounts.fields.bank_name'))
                            ->required()
                            ->maxLength(255)
                            ->disabled(fn ($get) => $get('account_type') === 'domestic')
                            ->dehydrated()
                            ->helperText(fn ($get) =>
                                $get('account_type') === 'domestic'
                                    ? 'Automatski popunjeno na osnovu izabrane banke'
                                    : null
                            ),

                        // Account number for domestic
                        TextInput::make('account_number')
                            ->label(__('bank_accounts.fields.account_number'))
                            ->required(fn ($get) => $get('account_type') === 'domestic')
                            ->visible(fn ($get) => $get('account_type') === 'domestic')
                            ->maxLength(255),

                        // IBAN for foreign
                        TextInput::make('iban')
                            ->label(__('bank_accounts.fields.iban'))
                            ->required(fn ($get) => $get('account_type') === 'foreign')
                            ->visible(fn ($get) => $get('account_type') === 'foreign')
                            ->maxLength(34)
                            ->placeholder('RS35260005601001611379'),

                        // SWIFT (readonly for domestic, manual for foreign)
                        TextInput::make('swift')
                            ->label(__('bank_accounts.fields.swift'))
                            ->maxLength(20)
                            ->disabled(fn ($get) => $get('account_type') === 'domestic')
                            ->dehydrated()
                            ->helperText(fn ($get) =>
                                $get('account_type') === 'domestic'
                                    ? __('bank_accounts.help.swift_auto')
                                    : null
                            ),

                        Select::make('currency')
                            ->label(__('bank_accounts.fields.currency'))
                            ->options(BankAccount::getCurrencies())
                            ->required()
                            ->default('RSD')
                            ->disabled(fn ($get) => $get('account_type') === 'domestic')
                            ->dehydrated()
                            ->helperText(fn ($get) =>
                                $get('account_type') === 'domestic'
                                    ? __('bank_accounts.help.domestic_currency')
                                    : null
                            ),

                        Toggle::make('is_primary')
                            ->label(__('bank_accounts.fields.is_primary'))
                            ->helperText(__('bank_accounts.help.is_primary')),
                    ])
                    ->columns(2)
                    ->defaultItems(0)
                    ->addActionLabel(__('bank_accounts.actions.add_account'))
                    ->reorderable()
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string =>
                        $state['bank_name'] ?? __('bank_accounts.new_account')
                    )
                    ->deleteAction(
                        fn ($action) => $action->requiresConfirmation()
                    ),
            ])
            ->statePath('data');
    }

    public function save()
    {
        $validated = $this->form->getState();

        $userCompany = UserCompany::where('user_id', Auth::id())->first();

        if (!$userCompany) {
            Notification::make()
                ->title(__('bank_accounts.notifications.company_required'))
                ->danger()
                ->send();
            return;
        }

        // Delete existing bank accounts and recreate
        $userCompany->bankAccounts()->delete();

        // Create new bank accounts
        foreach ($validated['bank_accounts'] ?? [] as $accountData) {
            $userCompany->bankAccounts()->create($accountData);
        }

        // Reload data
        $userCompany = $userCompany->fresh();
        $this->data['bank_accounts'] = $userCompany->bankAccounts->toArray();
        $this->form->fill($this->data);

        Notification::make()
            ->title(__('bank_accounts.notifications.saved'))
            ->success()
            ->send();
    }
}
