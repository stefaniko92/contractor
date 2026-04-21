<?php

namespace App\Services\Sef;

use App\Models\Client;
use App\Services\SefService;
use Illuminate\Support\Facades\Cache;
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
    public function resolveByPib(string $pib): RecipientData
    {
        // Strip RS prefix if present
        $pib = str_replace('RS', '', $pib);

        // Try cache first (cache for 24 hours)
        $cacheKey = "sef_recipient_{$pib}";
        $cached = Cache::get($cacheKey);
        if ($cached) {
            return RecipientData::fromArray($cached);
        }

        // Fetch from SEF
        $companies = $this->fetchCompanies();

        foreach ($companies as $company) {
            if ($company['VatRegistrationCode'] === $pib) {
                $data = new RecipientData(
                    vatNumber: $pib,
                    name: $company['Name'] ?? '',
                    registrationCode: $company['RegistrationCode'] ?? null,
                    jbkjs: $company['BugetCompanyNumber'] ?? null,
                    isBudgetUser: !empty($company['BugetCompanyNumber']),
                    isRegistered: true
                );

                // Cache the result
                Cache::put($cacheKey, $data->toArray(), 86400);

                return $data;
            }
        }

        // Not found in SEF
        return new RecipientData(
            vatNumber: $pib,
            name: '',
            registrationCode: null,
            jbkjs: null,
            isBudgetUser: false,
            isRegistered: false
        );
    }

    /**
     * Update client with SEF data
     */
    public function updateClientFromSef(Client $client): void
    {
        if (!$client->tax_id) {
            return;
        }

        $recipientData = $this->resolveByPib($client->tax_id);

        if ($recipientData->isRegistered) {
            $client->update([
                'jbkjs' => $recipientData->jbkjs,
                'registration_number' => $recipientData->registrationCode ?: $client->registration_number,
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
     * Fetch all companies from SEF
     */
    private function fetchCompanies(): array
    {
        // Cache the entire list for 6 hours
        return Cache::remember('sef_all_companies', 21600, function () {
            $response = $this->sefService->getAllCompanies();

            if (isset($response['error'])) {
                Log::error('Failed to fetch SEF companies', ['error' => $response['error']]);
                return [];
            }

            // Convert DTOs back to arrays for caching
            $companies = [];
            foreach ($response['companies'] ?? [] as $company) {
                if (is_object($company)) {
                    $companies[] = [
                        'VatRegistrationCode' => $company->getPib(),
                        'Name' => $company->name,
                        'RegistrationCode' => property_exists($company, 'registrationCode') ? $company->registrationCode : null,
                        'BugetCompanyNumber' => property_exists($company, 'bugetCompanyNumber') ? $company->bugetCompanyNumber : null,
                    ];
                } else {
                    $companies[] = $company;
                }
            }

            return $companies;
        });
    }

    /**
     * Clear cache for a specific PIB
     */
    public function clearCache(?string $pib = null): void
    {
        if ($pib) {
            Cache::forget("sef_recipient_{$pib}");
        } else {
            Cache::forget('sef_all_companies');
        }
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
        public readonly bool $isRegistered
    ) {}

    public function toArray(): array
    {
        return [
            'vatNumber' => $this->vatNumber,
            'name' => $this->name,
            'registrationCode' => $this->registrationCode,
            'jbkjs' => $this->jbkjs,
            'isBudgetUser' => $this->isBudgetUser,
            'isRegistered' => $this->isRegistered,
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
            isRegistered: $data['isRegistered']
        );
    }
}