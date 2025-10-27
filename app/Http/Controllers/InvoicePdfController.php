<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Gotenberg\Gotenberg;
use Gotenberg\Stream;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class InvoicePdfController extends Controller
{
    /**
     * Display the invoice HTML (for preview)
     */
    public function preview(Invoice $invoice)
    {
        // Ensure the user owns this invoice
        if ($invoice->user_id !== Auth::id()) {
            abort(403);
        }

        // Load relationships
        $invoice->load(['client', 'items', 'user.userCompany.companyOwner', 'bankAccount']);

        return view('pdf.invoice', compact('invoice'));
    }

    /**
     * Generate and download PDF using Gotenberg
     */
    public function download(Invoice $invoice)
    {
        // Ensure the user owns this invoice
        if ($invoice->user_id !== Auth::id()) {
            abort(403);
        }

        // Load relationships
        $invoice->load(['client', 'items', 'user.userCompany.companyOwner', 'bankAccount']);

        // Render the HTML
        $html = view('pdf.invoice', compact('invoice'))->render();

        // Get the CSS content
        $cssPath = public_path('css/invoice/style.css');
        $css = file_exists($cssPath) ? file_get_contents($cssPath) : '';

        // Get Gotenberg URL from environment (default to local)
        $gotenbergUrl = config('services.gotenberg.url', 'http://localhost:3000');

        try {
            // Create Gotenberg request
            $request = Gotenberg::chromium($gotenbergUrl)
                ->pdf();

            // Add CSS if exists (must be before html())
            if (! empty($css)) {
                $request = $request->assets(Stream::string('style.css', $css));
            }

            // Add HTML and set PDF options
            $request = $request
                ->html(Stream::string('index.html', $html));

            // Configure HTTP client with SSL options and API key
            $httpOptions = [
                'verify' => config('services.gotenberg.verify_ssl', false), // Disable SSL verification for self-signed certs
                'timeout' => 30,
            ];

            // Add API key authorization if configured
            if ($apiKey = config('services.gotenberg.api_key')) {
                $httpOptions['headers'] = [
                    'Authorization' => 'Basic '.$apiKey,
                ];
            }

            $httpClient = new Client($httpOptions);

            // Generate PDF with custom HTTP client
            $response = Gotenberg::send($request, $httpClient);

            // Extract PDF content from Guzzle response
            $pdfContent = $response->getBody()->getContents();

            // Determine filename based on document type
            $documentType = match ($invoice->invoice_document_type) {
                'profaktura' => 'Profaktura',
                'avansna_faktura' => 'Avansna-Faktura',
                default => 'Faktura'
            };

            $filename = "{$documentType}-{$invoice->invoice_number}.pdf";
            $filename = str_replace('/', '-', $filename); // Replace / with - for filesystem safety

            // Return PDF as download
            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]);

        } catch (\Exception $e) {
            // Log the error
            \Log::error('Gotenberg PDF generation failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'gotenberg_url' => $gotenbergUrl,
            ]);

            // Return error response
            return response()->json([
                'error' => 'PDF generation failed',
                'message' => 'Could not connect to Gotenberg service. Please ensure it is running.',
                'details' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Print invoice (same as download but with inline disposition)
     */
    public function print(Invoice $invoice)
    {
        // Ensure the user owns this invoice
        if ($invoice->user_id !== Auth::id()) {
            abort(403);
        }

        // Load relationships
        $invoice->load(['client', 'items', 'user.userCompany.companyOwner', 'bankAccount']);

        // Render the HTML
        $html = view('pdf.invoice', compact('invoice'))->render();

        // Get the CSS content
        $cssPath = public_path('css/invoice/style.css');
        $css = file_exists($cssPath) ? file_get_contents($cssPath) : '';

        // Get Gotenberg URL from environment
        $gotenbergUrl = config('services.gotenberg.url', 'http://localhost:3000');

        try {
            // Create Gotenberg request
            $request = Gotenberg::chromium($gotenbergUrl)
                ->pdf();

            // Add CSS if exists (must be before html())
            if (! empty($css)) {
                $request = $request->assets(Stream::string('style.css', $css));
            }

            // Add HTML and set PDF options
            $request = $request
                ->html(Stream::string('index.html', $html));

            // Configure HTTP client with SSL options and API key
            $httpOptions = [
                'verify' => config('services.gotenberg.verify_ssl', false), // Disable SSL verification for self-signed certs
                'timeout' => 30,
            ];

            // Add API key authorization if configured
            if ($apiKey = config('services.gotenberg.api_key')) {
                $httpOptions['headers'] = [
                    'Authorization' => 'Basic '.$apiKey,
                ];
            }

            $httpClient = new Client($httpOptions);

            // Generate PDF with custom HTTP client
            $response = Gotenberg::send($request, $httpClient);

            // Extract PDF content from Guzzle response
            $pdfContent = $response->getBody()->getContents();

            $documentType = match ($invoice->invoice_document_type) {
                'profaktura' => 'Profaktura',
                'avansna_faktura' => 'Avansna-Faktura',
                default => 'Faktura'
            };

            $filename = "{$documentType}-{$invoice->invoice_number}.pdf";
            $filename = str_replace('/', '-', $filename);

            // Return PDF for inline display (print)
            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$filename.'"',
            ]);

        } catch (\Exception $e) {
            \Log::error('Gotenberg PDF generation failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'PDF generation failed',
                'message' => 'Could not connect to Gotenberg service.',
                'details' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
