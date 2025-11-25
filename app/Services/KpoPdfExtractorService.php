<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\KpoEntry;
use App\Models\KpoUpload;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Prism\Prism\Facades\Prism;
use Prism\Prism\ValueObjects\Media\Document;

class KpoPdfExtractorService
{
    public function __construct(
        private readonly ClientMatchingService $clientMatchingService
    ) {}

    public function extractFromPdf(KpoUpload $kpoUpload): void
    {
        try {
            $kpoUpload->markAsProcessing();

            if (! Storage::disk('s3')->exists($kpoUpload->file_path)) {
                throw new \Exception("PDF file not found on S3: {$kpoUpload->file_path}");
            }

            $pdfContent = Storage::disk('s3')->get($kpoUpload->file_path);

            $extractedData = $this->extractDataUsingAI($pdfContent);

            $this->saveExtractedEntries($kpoUpload, $extractedData);

            $kpoUpload->markAsCompleted();

        } catch (\Exception $e) {
            Log::error('KPO PDF extraction failed', [
                'upload_id' => $kpoUpload->id,
                'error' => $e->getMessage(),
            ]);

            $kpoUpload->markAsFailed($e->getMessage());
            throw $e;
        }
    }

    protected function extractDataUsingAI(string $pdfContent): array
    {
        $document = Document::fromRawContent($pdfContent, 'application/pdf', 'KPO Book');

        $response = Prism::text()
            ->using('anthropic', 'claude-3-7-sonnet-20250219')
            ->withPrompt($this->getExtractionPrompt(), [$document])
            ->withMaxTokens(8000)
            ->withClientOptions([
                'timeout' => 120,
                'connect_timeout' => 30,
            ])
            ->asText();

        $content = $response->text;

        $jsonMatch = [];
        if (preg_match('/```json\s*(.*?)\s*```/s', $content, $jsonMatch)) {
            $jsonString = $jsonMatch[1];
        } else {
            $jsonString = $content;
        }

        $data = json_decode($jsonString, true);

        if (! $data || ! isset($data['entries'])) {
            throw new \Exception('Failed to extract valid data from PDF. AI response: '.substr($content, 0, 500));
        }

        return $data;
    }

    protected function getExtractionPrompt(): string
    {
        return <<<'PROMPT'
Molim te da ekstrahuješ podatke iz KPO knjige (Knjiga Primljenih i Ostvarenih Prihoda) koja je na srpskom jeziku.

Trebam sledeće informacije za svaki unos u tabeli:
- Redni broj (entry_number)
- Datum prijema (date) - u formatu YYYY-MM-DD
- Oznaka računa (invoice_mark)
- Naziv proizvoda ili usluge (product_service_description)
- Kupac/korisnik (client_name)
- Iznosi prihoda (income_amount) - samo broj, bez valute
- Iznosi rashoda (expense_amount) - samo broj, bez valute
- Valuta (currency) - RSD ako nije navedeno drugačije

Vrati podatke u JSON formatu:
{
  "entries": [
    {
      "entry_number": 1,
      "date": "2024-01-15",
      "invoice_mark": "BROJ-001",
      "product_service_description": "Opis usluge",
      "client_name": "Ime klijenta",
      "income_amount": 100000.00,
      "expense_amount": 0,
      "currency": "RSD"
    }
  ]
}

VAŽNO:
- Ignoriši prazne redove
- Za iznose koristi decimalni format sa dve decimale
- Ako neko polje nedostaje, stavi null
- Ako je iznos 0, stavi 0 a ne null
- Ako je iznos NEGATIVAN, to je STORNO (storniranje fakture) - ostavi ga kao negativan broj u income_amount
- Ekstrahuj SVE unose iz PDF-a, ne samo nekoliko primera
PROMPT;
    }

    protected function saveExtractedEntries(KpoUpload $kpoUpload, array $data): void
    {
        DB::transaction(function () use ($kpoUpload, $data) {
            $entries = $data['entries'] ?? [];
            $clientsCreated = 0;
            $invoicesCreated = 0;

            foreach ($entries as $entryData) {
                $clientId = null;
                $client = null;

                if (! empty($entryData['client_name'])) {
                    $result = $this->clientMatchingService->findOrCreateClient(
                        $kpoUpload->user_id,
                        $entryData['client_name']
                    );

                    $client = $result['client'];
                    $clientId = $client->id;
                    if ($result['created']) {
                        $clientsCreated++;
                    }
                }

                $incomeAmount = $entryData['income_amount'] ?? 0;
                $isStorno = $incomeAmount < 0;
                $invoiceId = null;

                // Create invoice for this entry (if it has income and client)
                if ($client && $incomeAmount != 0) {
                    $invoice = $this->createInvoiceFromEntry(
                        $kpoUpload->user_id,
                        $client,
                        $entryData,
                        $isStorno
                    );
                    $invoiceId = $invoice->id;
                    $invoicesCreated++;
                }

                $kpoEntry = KpoEntry::create([
                    'kpo_upload_id' => $kpoUpload->id,
                    'user_id' => $kpoUpload->user_id,
                    'client_id' => $clientId,
                    'invoice_id' => $invoiceId,
                    'entry_number' => $entryData['entry_number'] ?? null,
                    'date' => $entryData['date'] ?? null,
                    'invoice_mark' => $entryData['invoice_mark'] ?? null,
                    'product_service_description' => $entryData['product_service_description'] ?? null,
                    'client_name' => $entryData['client_name'] ?? null,
                    'income_amount' => $incomeAmount,
                    'expense_amount' => $entryData['expense_amount'] ?? 0,
                    'currency' => $entryData['currency'] ?? 'RSD',
                    'raw_data' => $entryData,
                ]);
            }

            $kpoUpload->update([
                'total_entries' => count($entries),
                'processed_entries' => count($entries),
                'clients_created' => $clientsCreated,
                'extraction_data' => $data,
            ]);

            Log::info('KPO extraction completed', [
                'upload_id' => $kpoUpload->id,
                'entries' => count($entries),
                'clients_created' => $clientsCreated,
                'invoices_created' => $invoicesCreated,
            ]);
        });
    }

    protected function createInvoiceFromEntry(int $userId, $client, array $entryData, bool $isStorno): Invoice
    {
        $date = $entryData['date'] ?? now()->format('Y-m-d');
        $year = date('Y', strtotime($date));
        $amount = abs($entryData['income_amount'] ?? 0);

        // Generate invoice number: get next number for this year
        $lastInvoice = Invoice::where('user_id', $userId)
            ->whereYear('issue_date', $year)
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = 1;
        if ($lastInvoice && preg_match('/^(\d+)\//', $lastInvoice->invoice_number, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        }

        $invoiceNumber = "{$nextNumber}/{$year}";

        $invoice = Invoice::create([
            'user_id' => $userId,
            'client_id' => $client->id,
            'invoice_number' => $invoiceNumber,
            'amount' => $amount,
            'description' => $entryData['product_service_description'] ?? 'Računarsko programiranje',
            'currency' => $entryData['currency'] ?? 'RSD',
            'issue_date' => $date,
            'due_date' => date('Y-m-d', strtotime($date.' +30 days')),
            'trading_place' => 'Beograd',
            'status' => $isStorno ? 'storned' : 'issued',
            'invoice_type' => 'domestic',
            'invoice_document_type' => 'faktura',
            'is_storno' => $isStorno,
        ]);

        // Create invoice item so the amount calculation works correctly
        $invoice->items()->create([
            'title' => $entryData['product_service_description'] ?? 'Računarsko programiranje',
            'description' => $entryData['product_service_description'] ?? 'Računarsko programiranje',
            'quantity' => 1,
            'unit_price' => $amount,
            'amount' => $amount,
            'unit' => 'usluga',
            'currency' => $entryData['currency'] ?? 'RSD',
            'type' => 'service',
        ]);

        // Refresh the invoice amount from items
        $invoice->updateAmount();

        return $invoice;
    }
}
