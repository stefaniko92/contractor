<?php

namespace App\Services;

use App\Models\EfakturaInvoice;
use App\Models\Invoice;
use App\Services\Sef\InvoiceValidator;
use App\Services\Sef\RecipientResolver;
use App\Services\Sef\VatProfileResolver;

class SefInvoiceSubmissionService
{
    /**
     * @return array{success: bool, error?: string, response?: array<string, mixed>}
     */
    public function submit(Invoice $invoice): array
    {
        $invoice->loadMissing(['client', 'efakturaInvoice']);
        $sefService = SefService::forUser($invoice->user_id);
        $availability = $sefService->getAvailabilityStatus();

        if (! $availability['available']) {
            return [
                'success' => false,
                'error' => $availability['message'],
            ];
        }

        $validation = (new InvoiceValidator(
            new VatProfileResolver($sefService),
            new RecipientResolver($sefService),
        ))->validate($invoice);

        if ($validation->hasErrors()) {
            return [
                'success' => false,
                'error' => implode(' ', $validation->errors),
            ];
        }

        $efakturaInvoice = EfakturaInvoice::forSubmission($invoice);
        $response = $sefService->sendInvoice(
            $invoice->generateUblXml(),
            filled($invoice->client->jbkjs) ? 'Yes' : 'No',
            $efakturaInvoice->sef_request_id,
        );

        if (isset($response['error'])) {
            $efakturaInvoice->update([
                'status' => 'failed',
                'last_error' => $response['error'],
                'last_error_at' => now(),
                'sef_response' => $response,
            ]);

            return [
                'success' => false,
                'error' => $response['error'],
                'response' => $response,
            ];
        }

        $efakturaInvoice->update([
            'sef_invoice_id' => $response['SalesInvoiceId'] ?? $response['salesInvoiceId'] ?? $response['InvoiceId'] ?? $response['invoiceId'] ?? $response['id'] ?? null,
            'sef_invoice_number' => $response['InvoiceNumber'] ?? $response['invoiceNumber'] ?? null,
            'sef_request_id' => $response['RequestId'] ?? $response['requestId'] ?? $efakturaInvoice->sef_request_id,
            'status' => 'sent',
            'sent_at' => now(),
            'last_error' => null,
            'last_error_at' => null,
            'sef_response' => $response,
            'status_history' => [[
                'from' => $invoice->efakturaInvoice?->status ?? 'draft',
                'to' => 'sent',
                'changed_at' => now()->toISOString(),
                'data' => $response,
            ]],
        ]);

        return [
            'success' => true,
            'response' => $response,
        ];
    }
}
