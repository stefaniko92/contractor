<?php

namespace App\Console\Commands;

use App\Services\ExchangeRateService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SyncExchangesRates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-exchanges-rates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync exchange rates';

    protected ExchangeRateService $exchangeRateService;

    public function __construct(ExchangeRateService $exchangeRateService)
    {
        parent::__construct();
        $this->exchangeRateService = $exchangeRateService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Replace this with your actual logic to fetch exchange rates
            $exchangeRates = $this->fetchExchangeRates();

            if ($exchangeRates) {
                // Cache the exchange rates on a persistent store (avoid Octane in-memory cache)
                $store = config('exchange.cache_store', 'file');
                $key = config('exchange.cache_key', 'exchange_rates');
                $ttl = now()->addMinutes((int) config('exchange.ttl_minutes', 1440));

                Cache::store($store)->put($key, $exchangeRates, $ttl);
                $this->info('Exchange rates successfully synced and cached.');
            } else {
                $this->error('Failed to sync exchange rates. No data retrieved.');
            }
        } catch (\Exception $e) {
            $this->error('An error occurred while syncing exchange rates: '.$e->getMessage());
        }
    }

    /**
     * Fetch exchange rates from the external service.
     *
     * @return array|null
     */
    private function fetchExchangeRates()
    {
        return $this->exchangeRateService->fetchCurrentExchangeRates();
    }
}
