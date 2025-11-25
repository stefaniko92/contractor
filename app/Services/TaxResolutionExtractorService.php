<?php

namespace App\Services;

use App\Models\TaxObligation;
use App\Models\TaxResolution;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Prism\Prism\Facades\Prism;
use Prism\Prism\ValueObjects\Media\Document;

class TaxResolutionExtractorService
{
    public function extractFromPdf(TaxResolution $taxResolution): void
    {
        try {
            $taxResolution->markAsProcessing();

            if (! Storage::disk('s3')->exists($taxResolution->file_path)) {
                throw new \Exception("PDF file not found on S3: {$taxResolution->file_path}");
            }

            $pdfContent = Storage::disk('s3')->get($taxResolution->file_path);
            $extractedData = $this->extractDataUsingAI($pdfContent, $taxResolution->type);
            $this->saveExtractedObligation($taxResolution, $extractedData);
            $taxResolution->markAsCompleted();

        } catch (\Exception $e) {
            Log::error('Tax resolution extraction failed', [
                'resolution_id' => $taxResolution->id,
                'error' => $e->getMessage(),
            ]);
            $taxResolution->markAsFailed($e->getMessage());
            throw $e;
        }
    }

    protected function extractDataUsingAI(string $pdfContent, string $type): array
    {
        $document = Document::fromRawContent($pdfContent, 'application/pdf', 'Tax Resolution');

        $response = Prism::text()
            ->using('anthropic', 'claude-3-7-sonnet-20250219')
            ->withPrompt($this->getExtractionPrompt($type), [$document])
            ->withMaxTokens(4000)
            ->withClientOptions(['timeout' => 120, 'connect_timeout' => 30])
            ->asText();

        $content = $response->text;

        if (preg_match('/```json\s*(.*?)\s*```/s', $content, $match)) {
            $jsonString = $match[1];
        } else {
            $jsonString = $content;
        }

        $data = json_decode($jsonString, true);

        if (! $data) {
            throw new \Exception('Failed to extract data from PDF. AI response: '.substr($content, 0, 500));
        }

        return $data;
    }

    protected function getExtractionPrompt(string $type): string
    {
        $typeName = $type === 'pio' ? 'PIO (penzijsko i invalidsko osiguranje)' : 'poreza na prihode';

        return <<<PROMPT
Ekstrahuj podatke iz poreskog rešenja za {$typeName} na srpskom jeziku.

Trebam sledeće informacije:
- Godina (year) - za koju godinu važi rešenje
- Broj rešenja (resolution_number) - ako postoji
- Iznos obaveze (amount) - mesečni iznos obaveze u dinarima
- Šifra plaćanja (payment_code) - obično 253
- Račun primaoca (payment_recipient_account) - broj računa primaoca
- Model (payment_model) - obično 97
- Poziv na broj (payment_reference) - referentni broj za plaćanje

Vrati podatke u JSON formatu:
{
  "year": 2025,
  "resolution_number": "2570878023",
  "monthly_amount": 15885.64,
  "payment_code": "253",
  "payment_recipient_account": "160-0000000442249-95",
  "payment_model": "97",
  "payment_reference": "8812790000007230954"
}

VAŽNO: Iznos treba biti mesečna obaveza, ne godišnja.
PROMPT;
    }

    protected function saveExtractedObligation(TaxResolution $taxResolution, array $data): void
    {
        DB::transaction(function () use ($taxResolution, $data) {
            $description = $taxResolution->type === 'pio' ? 'DOPRINOS ZA PIO' : 'POREZ NA PRIHODE OD SAMOSTALNE DELATNOSTI';

            // Create one obligation per month for the year
            for ($month = 1; $month <= 12; $month++) {
                TaxObligation::create([
                    'user_id' => $taxResolution->user_id,
                    'tax_resolution_id' => $taxResolution->id,
                    'type' => $taxResolution->type,
                    'description' => $description,
                    'amount' => $data['monthly_amount'] ?? 0,
                    'currency' => 'RSD',
                    'year' => $data['year'] ?? now()->year,
                    'month' => $month,
                    'due_date' => now()->setYear($data['year'] ?? now()->year)->setMonth($month)->setDay(15),
                    'payment_code' => $data['payment_code'] ?? '253',
                    'payment_recipient_account' => $data['payment_recipient_account'] ?? null,
                    'payment_payer_account' => $data['payment_payer_account'] ?? null,
                    'payment_model' => $data['payment_model'] ?? '97',
                    'payment_reference' => $data['payment_reference'] ?? null,
                ]);
            }

            $taxResolution->update([
                'resolution_number' => $data['resolution_number'] ?? null,
                'extraction_data' => $data,
            ]);
        });
    }
}
