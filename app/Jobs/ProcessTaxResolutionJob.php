<?php

namespace App\Jobs;

use App\Models\TaxResolution;
use App\Services\TaxResolutionExtractorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessTaxResolutionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;

    public int $tries = 1;

    public function __construct(
        public TaxResolution $taxResolution
    ) {}

    public function handle(TaxResolutionExtractorService $extractorService): void
    {
        try {
            $extractorService->extractFromPdf($this->taxResolution);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $this->taxResolution->markAsFailed($exception->getMessage());
    }
}
