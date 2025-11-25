<?php

namespace App\Services;

use App\Models\Client;

class ClientMatchingService
{
    public function findOrCreateClient(int $userId, string $clientName): array
    {
        $normalizedName = $this->normalizeClientName($clientName);

        $existingClient = Client::query()
            ->where('user_id', $userId)
            ->whereRaw('LOWER(company_name) = ?', [strtolower($normalizedName)])
            ->first();

        if ($existingClient) {
            return [
                'client' => $existingClient,
                'created' => false,
            ];
        }

        $client = Client::create([
            'user_id' => $userId,
            'company_name' => $normalizedName,
        ]);

        return [
            'client' => $client,
            'created' => true,
        ];
    }

    protected function normalizeClientName(string $name): string
    {
        return trim($name);
    }
}
