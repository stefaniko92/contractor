<?php

namespace App\Console\Commands;

use App\Services\Sef\VatProfileResolver;
use App\Services\SefService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SyncSefData extends Command
{
    protected $signature = 'sef:sync
                          {--user-id= : User ID to sync for (optional)}
                          {--force : Force refresh cache}';

    protected $description = 'Sync VAT exemption reasons and company list from SEF API';

    public function handle(): int
    {
        $userId = $this->option('user-id') ?? 1;

        $this->info('Syncing SEF data...');

        try {
            $sefService = SefService::forUser((int) $userId);

            if (!$sefService->isConfigured()) {
                $this->error('SEF is not configured for this user');
                return self::FAILURE;
            }

            // Clear cache if forced
            if ($this->option('force')) {
                Cache::forget('sef_vat_exemption_reasons');
                Cache::forget('sef_all_companies');
                $this->info('Cache cleared');
            }

            // Sync VAT exemption reasons
            $this->info('Fetching VAT exemption reasons...');
            $vatResolver = new VatProfileResolver($sefService);
            $reasons = $vatResolver->getExemptionReasons();

            $this->table(
                ['Code', 'Description'],
                array_map(fn($r) => [$r['code'] ?? 'N/A', $r['description'] ?? 'N/A'], $reasons)
            );

            $this->info(sprintf('✓ Synced %d VAT exemption reasons', count($reasons)));

            // Sync companies
            $this->info('Fetching companies from SEF...');
            $companiesResponse = $sefService->getAllCompanies();

            if (isset($companiesResponse['error'])) {
                $this->error('Failed to fetch companies: ' . $companiesResponse['error']);
                return self::FAILURE;
            }

            $companies = $companiesResponse['companies'] ?? [];
            $budgetUsersCount = 0;

            foreach ($companies as $company) {
                $isBudgetUser = !empty($company->bugetCompanyNumber ?? null);
                if ($isBudgetUser) {
                    $budgetUsersCount++;
                }
            }

            $this->info(sprintf('✓ Synced %d companies (%d budget users)', count($companies), $budgetUsersCount));

            $this->newLine();
            $this->info('✓ SEF data synced successfully');

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Failed to sync SEF data: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
