<?php

namespace App\Filament\Resources\Clients\Pages;

use App\Filament\Resources\Clients\ClientResource;
use App\Services\SefService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;

class EditClient extends EditRecord
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('verify_efaktura')
                ->label('Proveri u eFaktura')
                ->icon('heroicon-o-shield-check')
                ->color('info')
                ->visible(fn () => ! empty($this->record->tax_id))
                ->badge(function () {
                    if ($this->record->efaktura_status === 'active') {
                        return 'Verifikovan';
                    } elseif ($this->record->efaktura_verified && $this->record->efaktura_status === 'not_found') {
                        return 'Nije pronađen';
                    } elseif ($this->record->efaktura_verified && $this->record->efaktura_status === 'error') {
                        return 'Greška';
                    }

                    return 'Nije provereno';
                })
                ->badgeColor(function () {
                    return match ($this->record->efaktura_status) {
                        'active' => 'success',
                        'not_found' => 'warning',
                        'error' => 'danger',
                        default => 'gray',
                    };
                })
                ->requiresConfirmation()
                ->modalHeading('Proveri klijenta u eFaktura sistemu')
                ->modalDescription(fn () => "Da li želite da proverite da li klijent \"{$this->record->company_name}\" (PIB: {$this->record->tax_id}) postoji u eFaktura sistemu?")
                ->modalSubmitActionLabel(__('actions.confirm'))
                ->action(function () {
                    try {
                        $sefService = SefService::forUser($this->record->user_id);

                        // Check SEF availability
                        $availabilityStatus = $sefService->getAvailabilityStatus();
                        if (! $availabilityStatus['available']) {
                            Notification::make()
                                ->title('SEF nije dostupan')
                                ->body($availabilityStatus['message'])
                                ->warning()
                                ->send();

                            return;
                        }

                        // Search for company by PIB
                        $result = $sefService->searchCompanyByPib($this->record->tax_id);

                        if (isset($result['error'])) {
                            // API error
                            $this->record->update([
                                'efaktura_verified' => true,
                                'efaktura_verified_at' => now(),
                                'efaktura_status' => 'error',
                                'efaktura_verification_error' => $result['error'],
                            ]);

                            Notification::make()
                                ->title('Greška pri verifikaciji')
                                ->body($result['error'])
                                ->danger()
                                ->send();

                            Log::error('eFaktura verification error', [
                                'client_id' => $this->record->id,
                                'tax_id' => $this->record->tax_id,
                                'error' => $result['error'],
                            ]);
                        } elseif (! empty($result['companies'])) {
                            // Company found
                            $this->record->update([
                                'efaktura_verified' => true,
                                'efaktura_verified_at' => now(),
                                'efaktura_status' => 'active',
                                'efaktura_verification_error' => null,
                            ]);

                            Notification::make()
                                ->title('Klijent pronađen!')
                                ->body("Klijent \"{$this->record->company_name}\" postoji u eFaktura sistemu i možete mu slati fakture.")
                                ->success()
                                ->send();

                            Log::info('Client verified in eFaktura', [
                                'client_id' => $this->record->id,
                                'tax_id' => $this->record->tax_id,
                                'company_name' => $this->record->company_name,
                            ]);
                        } else {
                            // Company not found
                            $this->record->update([
                                'efaktura_verified' => true,
                                'efaktura_verified_at' => now(),
                                'efaktura_status' => 'not_found',
                                'efaktura_verification_error' => null,
                            ]);

                            Notification::make()
                                ->title('Klijent nije pronađen')
                                ->body("Klijent \"{$this->record->company_name}\" ne postoji u eFaktura sistemu. Ne možete mu slati fakture elektronski, ali možete omogućiti ručno slanje.")
                                ->warning()
                                ->send();

                            Log::info('Client not found in eFaktura', [
                                'client_id' => $this->record->id,
                                'tax_id' => $this->record->tax_id,
                                'company_name' => $this->record->company_name,
                            ]);
                        }
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Greška')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();

                        Log::error('Exception during client verification', [
                            'client_id' => $this->record->id,
                            'tax_id' => $this->record->tax_id,
                            'exception' => $e->getMessage(),
                        ]);
                    }
                }),

            DeleteAction::make(),
        ];
    }
}
