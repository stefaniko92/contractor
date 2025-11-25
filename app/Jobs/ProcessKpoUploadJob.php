<?php

namespace App\Jobs;

use App\Models\KpoUpload;
use App\Services\KpoPdfExtractorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessKpoUploadJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 600;

    public int $tries = 1;

    public function __construct(
        public KpoUpload $kpoUpload
    ) {}

    public function handle(KpoPdfExtractorService $extractorService): void
    {
        try {
            $extractorService->extractFromPdf($this->kpoUpload);

            // Success - notification will be shown when user next visits the page

        } catch (\Exception $e) {
            // Error will be logged and visible in the kpo_uploads table
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $this->kpoUpload->markAsFailed($exception->getMessage());
    }
}
