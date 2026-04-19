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
            Action::make('manual_verify')
                ->label('Označi kao verifikovan')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $this->record->efaktura_status !== 'active' && ! empty($this->record->tax_id))
                ->requiresConfirmation()
                ->modalHeading('Ručna verifikacija klijenta')
                ->modalDescription(fn () => "Da li ste sigurni da klijent \"{$this->record->company_name}\" (PIB: {$this->record->tax_id}) postoji u SEF/eFaktura sistemu? Ovo će omogućiti slanje faktura ovom klijentu.")
                ->modalSubmitActionLabel(__('actions.confirm'))
                ->action(function () {
                    $this->record->update([
                        'efaktura_verified' => true,
                        'efaktura_verified_at' => now(),
                        'efaktura_status' => 'active',
                        'efaktura_verification_error' => 'Ručno verifikovan',
                    ]);

                    Notification::make()
                        ->title('Klijent označen kao verifikovan')
                        ->body("Možete sada slati fakture klijentu \"{$this->record->company_name}\" putem eFaktura sistema.")
                        ->success()
                        ->send();

                    Log::info('Client manually verified', [
                        'client_id' => $this->record->id,
                        'tax_id' => $this->record->tax_id,
                        'company_name' => $this->record->company_name,
                        'verified_by' => auth()->id(),
                    ]);
                }),

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
                            // API error - likely 404 on production
                            $errorMessage = $result['error'];
                            $isNotFoundError = str_contains($errorMessage, '404');

                            $this->record->update([
                                'efaktura_verified' => true,
                                'efaktura_verified_at' => now(),
                                'efaktura_status' => 'error',
                                'efaktura_verification_error' => $errorMessage,
                            ]);

                            if ($isNotFoundError) {
                                Notification::make()
                                    ->title('Automatska verifikacija nije dostupna')
                                    ->body('SEF API ne podržava automatsku verifikaciju na produkciji. Ako znate da klijent postoji u SEF sistemu, koristite "Označi kao verifikovan" dugme ili omogućite "Dozvoli slanje bez verifikacije" opciju.')
                                    ->warning()
                                    ->persistent()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Greška pri verifikaciji')
                                    ->body($errorMessage)
                                    ->danger()
                                    ->send();
                            }

                            Log::error('eFaktura verification error', [
                                'client_id' => $this->record->id,
                                'tax_id' => $this->record->tax_id,
                                'error' => $errorMessage,
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
