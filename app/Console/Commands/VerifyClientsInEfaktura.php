<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Services\SefService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class VerifyClientsInEfaktura extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'efaktura:verify-clients
                            {--user= : User ID to verify clients for}
                            {--force : Re-verify already verified clients}
                            {--limit=50 : Maximum number of clients to verify per run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify which clients exist in the eFaktura system by checking their PIB/Tax ID';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->option('user');
        $force = $this->option('force');
        $limit = (int) $this->option('limit');

        $this->info('Starting eFaktura client verification...');

        // Get clients that need verification
        $query = Client::query()
            ->whereNotNull('tax_id')
            ->where('tax_id', '!=', '');

        if (! $force) {
            $query->where('efaktura_verified', false);
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $clients = $query->limit($limit)->get();

        if ($clients->isEmpty()) {
            $this->info('No clients need verification.');

            return Command::SUCCESS;
        }

        $this->info("Found {$clients->count()} clients to verify.");

        $verified = 0;
        $found = 0;
        $notFound = 0;
        $errors = 0;

        $progressBar = $this->output->createProgressBar($clients->count());
        $progressBar->start();

        foreach ($clients as $client) {
            try {
                $sefService = SefService::forUser($client->user_id);

                // Search for company by PIB in eFaktura
                $result = $sefService->searchCompanyByPib($client->tax_id);

                if (isset($result['error'])) {
                    // API error
                    $client->update([
                        'efaktura_verified' => true,
                        'efaktura_verified_at' => now(),
                        'efaktura_status' => 'error',
                        'efaktura_verification_error' => $result['error'],
                    ]);
                    $errors++;
                } elseif (! empty($result['companies'])) {
                    // Company found
                    $client->update([
                        'efaktura_verified' => true,
                        'efaktura_verified_at' => now(),
                        'efaktura_status' => 'active',
                        'efaktura_verification_error' => null,
                    ]);
                    $found++;
                } else {
                    // Company not found
                    $client->update([
                        'efaktura_verified' => true,
                        'efaktura_verified_at' => now(),
                        'efaktura_status' => 'not_found',
                        'efaktura_verification_error' => null,
                    ]);
                    $notFound++;
                }

                $verified++;

                // Rate limiting - wait a bit between requests
                usleep(200000); // 200ms delay

            } catch (\Exception $e) {
                Log::error('Error verifying client in eFaktura', [
                    'client_id' => $client->id,
                    'tax_id' => $client->tax_id,
                    'error' => $e->getMessage(),
                ]);

                $client->update([
                    'efaktura_verified' => true,
                    'efaktura_verified_at' => now(),
                    'efaktura_status' => 'error',
                    'efaktura_verification_error' => $e->getMessage(),
                ]);

                $errors++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("Verification complete!");
        $this->table(
            ['Status', 'Count'],
            [
                ['Found in eFaktura', $found],
                ['Not found', $notFound],
                ['Errors', $errors],
                ['Total verified', $verified],
            ]
        );

        Log::info('eFaktura client verification completed', [
            'found' => $found,
            'not_found' => $notFound,
            'errors' => $errors,
            'total' => $verified,
        ]);

        return Command::SUCCESS;
    }
}
