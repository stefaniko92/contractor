<?php

namespace App\Services\Sef;

use App\Models\Client;
use App\Services\SefService;
use Illuminate\Support\Facades\Log;

class RecipientResolver
{
    private SefService $sefService;

    public function __construct(SefService $sefService)
    {
        $this->sefService = $sefService;
    }

    /**
     * Resolve recipient data from SEF by PIB
     */
    public function resolveByPib(string $pib, ?string $registrationNumber = null, ?string $jbkjs = null): RecipientData
    {
        // Strip RS prefix if present
        $pib = str_replace('RS', '', $pib);

        $response = $this->sefService->searchCompanyByPib($pib, $registrationNumber, $jbkjs);

        if (isset($response['error'])) {
            return RecipientData::withError($pib, $response['error']);
        }

        if ($response['requires_jbkjs'] ?? false) {
            return RecipientData::requiresJbkjs($pib);
        }

        if ($response['is_registered'] ?? false) {
            $company = $response['companies'][0] ?? [];

            return new RecipientData(
                vatNumber: $pib,
                name: $company['name'] ?? '',
                registrationCode: $company['registration_number'] ?? $registrationNumber,
                jbkjs: $company['jbkjs'] ?? $jbkjs,
                isBudgetUser: ! empty($company['jbkjs'] ?? $jbkjs),
                isRegistered: true,
                requiresJbkjs: false,
                error: null
            );
        }

        // Not found in SEF
        return new RecipientData(
            vatNumber: $pib,
            name: '',
            registrationCode: null,
            jbkjs: null,
            isBudgetUser: false,
            isRegistered: false,
            requiresJbkjs: false,
            error: null
        );
    }

    /**
     * Update client with SEF data
     */
    public function updateClientFromSef(Client $client): void
    {
        if (! $client->tax_id) {
            return;
        }

        $recipientData = $this->resolveByPib($client->tax_id, $client->registration_number, $client->jbkjs);

        if ($recipientData->error || $recipientData->requiresJbkjs) {
            $client->update([
                'efaktura_verified' => true,
                'efaktura_verified_at' => now(),
                'efaktura_status' => 'error',
                'efaktura_verification_error' => $recipientData->error
                    ?? 'Klijent je budžetski korisnik. Unesite JBKJS pre slanja na SEF.',
            ]);

            return;
        }

        if ($recipientData->isRegistered) {
            $client->update([
                'jbkjs' => $recipientData->jbkjs ?: $client->jbkjs,
                'registration_number' => $recipientData->registrationCode ?: $client->registration_number,
                'efaktura_verified' => true,
                'efaktura_status' => 'active',
                'efaktura_verified_at' => now(),
                'efaktura_verification_error' => null,
            ]);

            Log::info('Client updated from SEF', [
                'client_id' => $client->id,
                'pib' => $client->tax_id,
                'is_budget_user' => $recipientData->isBudgetUser,
                'jbkjs' => $recipientData->jbkjs,
            ]);
        }
    }

    /**
     * Clear cache for a specific PIB
     */
    public function clearCache(?string $pib = null): void
    {
        Log::debug('SEF recipient cache clear requested, but recipient lookups are no longer cached.', [
            'pib' => $pib,
        ]);
    }
}

/**
 * Data Transfer Object for recipient information
 */
class RecipientData
{
    public function __construct(
        public readonly string $vatNumber,
        public readonly string $name,
        public readonly ?string $registrationCode,
        public readonly ?string $jbkjs,
        public readonly bool $isBudgetUser,
        public readonly bool $isRegistered,
        public readonly bool $requiresJbkjs = false,
        public readonly ?string $error = null,
    ) {}

    public static function requiresJbkjs(string $vatNumber): self
    {
        return new self(
            vatNumber: $vatNumber,
            name: '',
            registrationCode: null,
            jbkjs: null,
            isBudgetUser: true,
            isRegistered: false,
            requiresJbkjs: true,
            error: null
        );
    }

    public static function withError(string $vatNumber, string $error): self
    {
        return new self(
            vatNumber: $vatNumber,
            name: '',
            registrationCode: null,
            jbkjs: null,
            isBudgetUser: false,
            isRegistered: false,
            requiresJbkjs: false,
            error: $error
        );
    }

    public function toArray(): array
    {
        return [
            'vatNumber' => $this->vatNumber,
            'name' => $this->name,
            'registrationCode' => $this->registrationCode,
            'jbkjs' => $this->jbkjs,
            'isBudgetUser' => $this->isBudgetUser,
            'isRegistered' => $this->isRegistered,
            'requiresJbkjs' => $this->requiresJbkjs,
            'error' => $this->error,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            vatNumber: $data['vatNumber'],
            name: $data['name'],
            registrationCode: $data['registrationCode'],
            jbkjs: $data['jbkjs'],
            isBudgetUser: $data['isBudgetUser'],
            isRegistered: $data['isRegistered'],
            requiresJbkjs: $data['requiresJbkjs'] ?? false,
            error: $data['error'] ?? null
        );
    }
}
