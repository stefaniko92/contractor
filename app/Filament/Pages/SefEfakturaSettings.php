<?php

namespace App\Filament\Pages;

use App\Models\SefEfakturaSetting;
use BackedEnum;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class SefEfakturaSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog;

    protected string $view = 'filament.pages.sef-efaktura-settings';

    protected static ?string $title = 'SEF/EFaktura Konfiguracija';

    protected static ?string $navigationLabel = 'SEF/EFaktura';

    protected static string|\UnitEnum|null $navigationGroup = 'Postavke';

    protected static ?int $navigationSort = 20;

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public SefEfakturaSetting $record;

    public ?array $data = [];

    public function form(Schema $form): Schema
    {
        return $form
            ->schema($this->getFormSchema())
            ->model($this->record)   // bind your settings model
            ->statePath('data');     // use your public ?array $data
    }

    public function mount(): void
    {
        $userId = Auth::id();
        $this->record = SefEfakturaSetting::firstOrCreate(
            ['user_id' => $userId],
            [
                'default_vat_exemption' => 'PDV-RS-33',
                'default_vat_category' => 'SS',
            ]
        );

        $this->form->fill($this->record->toArray());
    }

    protected function getFormSchema(): array
    {
        return [
            SchemaComponents\Section::make('EFaktura Integracija')
                ->description('Omogućite SEF/EFaktura integraciju i unesite API ključ')
                ->schema([
                    Forms\Components\Toggle::make('is_enabled')
                        ->label('Omogući SEF/EFaktura')
                        ->default(false)
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('api_key')
                        ->label('API ključ')
                        ->password()
                        ->revealable()
                        ->columnSpanFull()
                        ->helperText('Vaš API ključ od SEF/EFaktura servisa'),
                ]),

            SchemaComponents\Section::make('Zadane vrednosti')
                ->description('Postavite zadane vrednosti za sve nove fakture')
                ->schema([
                    Forms\Components\Select::make('default_vat_exemption')
                        ->label('Podrazumevano izuzeće od PDV-a')
                        ->options([
                            'PDV-RS-33' => 'PDV-RS-33',
                            'PDV-RS-35-7' => 'PDV-RS-35-7',
                            'PDV-RS-36-5' => 'PDV-RS-36-5',
                            'PDV-RS-36b-4' => 'PDV-RS-36b-4',
                        ])
                        ->afterStateHydrated(function (SchemaComponents\Utilities\Set $set, $state) {
                            if ($state === null || $state === '') {
                                $set('default_vat_exemption', 'PDV-RS-33');
                            }
                        })
                        ->required()
                        ->helperText('Izaberite zadanu PDV osnovu'),
                    Forms\Components\TextInput::make('default_vat_category')
                        ->label('Podrazumevana PDV kategorija')
                        ->afterStateHydrated(function (SchemaComponents\Utilities\Set $set, $state) {
                            if ($state === null || $state === '') {
                                $set('default_vat_category', 'SS');
                            }
                        })
                        ->required()
                        ->disabled()
                        ->dehydrated()
                        ->helperText('Kategorija PDV-a (fiksno SS)'),
                ])
                ->columns(2),

            SchemaComponents\Section::make('Webhook Konfiguracija')
                ->description('Kopirajte URL i dodajte ga u SEF/EFaktura servis')
                ->schema([
                    Forms\Components\Placeholder::make('webhook_url')
                        ->label('Webhook URL')
                        ->content(fn (): string => $this->generateWebhookUrl())
                        ->columnSpanFull(),
                    Forms\Components\Placeholder::make('webhook_help')
                        ->label('Napomena')
                        ->content('Kopirajte gornji URL i dodajte ga u konfiguraciji SEF/EFaktura servisa kako biste primali obaveštenja kada se fakture promene.')
                        ->columnSpanFull(),
                ]),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $this->record->update($data);

        // Generate webhook URL if enabled
        if ($data['is_enabled']) {
            $data['webhook_url'] = $this->generateWebhookUrl();
            $this->record->update(['webhook_url' => $data['webhook_url']]);
        }

        Notification::make()
            ->success()
            ->title('Postavke uspešno sačuvane')
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('Sačuva postavke')
                ->submit('save')
                ->color('primary'),
        ];
    }

    private function generateWebhookUrl(): string
    {
        $userId = Auth::id();
        $token = hash('sha256', $userId.($this->record->created_at?->timestamp ?? time()));

        return route('webhooks.sef-efaktura', [
            'user_id' => $userId,
            'token' => $token,
        ], absolute: true);
    }
}
