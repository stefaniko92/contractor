<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PibLookupService
{
    protected string $baseUrl = 'https://europe-west1-collab-pdf-api.cloudfunctions.net/api/pib';

    /**
     * Fetch company data by PIB from the Firebase function.
     */
    public function fetchByPib(string $pib): array
    {
        try {
            $url = "{$this->baseUrl}/{$pib}";

            Log::info('Fetching company data by PIB', [
                'pib' => $pib,
                'url' => $url,
            ]);

            $response = Http::timeout(30)->get($url);

            if (! $response->successful()) {
                Log::error('PIB lookup failed', [
                    'pib' => $pib,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'error' => 'Greška pri preuzimanju podataka. HTTP status: '.$response->status(),
                ];
            }

            $data = $response->json();

            if (! isset($data['success']) || ! $data['success']) {
                Log::warning('PIB lookup returned unsuccessful response', [
                    'pib' => $pib,
                    'response' => $data,
                ]);

                return [
                    'success' => false,
                    'error' => $data['error'] ?? 'Kompanija sa datim PIB-om nije pronađena.',
                ];
            }

            Log::info('PIB lookup successful', [
                'pib' => $pib,
                'company_name' => $data['data']['naziv'] ?? null,
            ]);

            return [
                'success' => true,
                'data' => $data['data'],
            ];

        } catch (\Exception $e) {
            Log::error('Exception during PIB lookup', [
                'pib' => $pib,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Greška pri preuzimanju podataka: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Transform the fetched data into a format suitable for the Client model.
     */
    public function transformToClientData(array $fetchedData): array
    {
        if (! isset($fetchedData['data'])) {
            return [];
        }

        $data = $fetchedData['data'];

        // Extract city from "mesto" field (e.g., "Ниш (Палилула)" -> "Niš")
        $mesto = $data['mesto'] ?? '';
        $city = $mesto;
        if (preg_match('/^([^\(]+)/', $mesto, $matches)) {
            $city = trim($matches[1]);
        }

        return [
            'company_name' => $data['naziv'] ?? null,
            'tax_id' => $data['pib'] ?? null,
            'registration_number' => $data['mbr'] ?? null,
            'address' => $data['adresa'] ?? null,
            'default_place_of_sale' => $city,
            'is_domestic' => true,
            'client_type' => 'pravno_lice', // Default to legal entity
        ];
    }
}
