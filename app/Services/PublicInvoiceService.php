<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use App\Models\UserCompany;
use Gotenberg\Gotenberg;
use Gotenberg\Stream;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PublicInvoiceService
{
    /**
     * Handle the public invoice generation
     */
    public function handle(array $data): array
    {
        return DB::transaction(function () use ($data) {
            // Find or create user
            $user = $this->findOrCreateUser($data['email'], $data['seller']);
            $isNewUser = $user->wasRecentlyCreated;

            // Create client
            $client = $this->createClient($user, $data['buyer'], $data['invoice_type']);

            // Create invoice with items
            $invoice = $this->createInvoice(
                $user,
                $client,
                $data['invoice'],
                $data['items'],
                $data['invoice_type']
            );

            // Generate PDF
            $pdfPath = $this->generatePdf($invoice);

            // Send email and get reset URL if new user
            $resetUrl = $this->sendEmail($user, $invoice, $pdfPath, $isNewUser);

            return [
                'user_created' => $isNewUser,
                'invoice' => $invoice,
                'reset_url' => $resetUrl,
            ];
        });
    }

    /**
     * Find existing user by email or create new one
     */
    private function findOrCreateUser(string $email, array $sellerData): User
    {
        $user = User::where('email', $email)->first();

        if ($user) {
            return $user;
        }

        // Create new user
        $user = User::create([
            'name' => $sellerData['company_name'],
            'email' => $email,
            'password' => bcrypt(Str::random(32)), // Random password, user will set via reset link
            'company_name' => $sellerData['company_name'],
            'tax_id' => $sellerData['pib'],
            'address' => $sellerData['address'],
            'phone' => $sellerData['phone'] ?? null,
            'default_currency' => 'RSD',
        ]);

        // Create user company
        UserCompany::create([
            'user_id' => $user->id,
            'company_name' => $sellerData['company_name'],
            'company_tax_id' => $sellerData['pib'],
            'company_registry_number' => $sellerData['mb'] ?? null,
            'company_address' => $sellerData['address'],
            'company_city' => $sellerData['city'] ?? null,
            'company_phone' => $sellerData['phone'] ?? null,
        ]);

        return $user;
    }

    /**
     * Create client for the user
     */
    private function createClient(User $user, array $buyerData, string $invoiceType): Client
    {
        return Client::create([
            'user_id' => $user->id,
            'company_name' => $buyerData['name'],
            'tax_id' => $buyerData['pib'] ?? null,
            'address' => $buyerData['address'],
            'city' => $buyerData['city'] ?? null,
            'country' => $buyerData['country'] ?? null,
            'is_domestic' => $invoiceType === 'domaca' ? 1 : 0,
            'client_type' => 'company',
        ]);
    }

    /**
     * Create invoice with items
     */
    private function createInvoice(User $user, Client $client, array $invoiceData, array $itemsData, string $invoiceType): Invoice
    {
        // Map API invoice_type to database enum
        $dbInvoiceType = $invoiceType === 'domaca' ? 'domestic' : 'foreign';

        // Create invoice
        $invoice = Invoice::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'invoice_number' => $invoiceData['number'] ?? null, // Will auto-generate if empty
            'issue_date' => $invoiceData['date_issued'],
            'due_date' => $invoiceData['date_due'],
            'trading_place' => $invoiceData['place'],
            'currency' => $invoiceData['currency'],
            'description' => $invoiceData['note'] ?? '',
            'amount' => 0, // Will be updated automatically by Invoice model
            'status' => 'issued',
            'invoice_type' => $dbInvoiceType,
            'invoice_document_type' => 'faktura',
        ]);

        // Create invoice items
        foreach ($itemsData as $itemData) {
            $this->createInvoiceItem($invoice, $itemData);
        }

        // Refresh to get updated amount
        $invoice->refresh();

        return $invoice;
    }

    /**
     * Create single invoice item with discount calculation
     */
    private function createInvoiceItem(Invoice $invoice, array $itemData): InvoiceItem
    {
        $quantity = $itemData['quantity'];
        $unitPrice = $itemData['unit_price'];
        $subtotal = $quantity * $unitPrice;

        // Calculate discount
        $discountValue = $itemData['discount_value'] ?? 0;
        $discountType = $itemData['discount_type'] ?? null;

        if ($discountValue > 0 && $discountType) {
            if ($discountType === '%') {
                $discountAmount = $subtotal * ($discountValue / 100);
                $finalAmount = $subtotal - $discountAmount;
            } else {
                // Fixed currency discount
                $finalAmount = max(0, $subtotal - $discountValue);
            }
        } else {
            $finalAmount = $subtotal;
        }

        // Map API type to database type
        $type = match ($itemData['type']) {
            'usluga' => 'service',
            'proizvod' => 'product',
            default => 'service',
        };

        // Map discount type
        $mappedDiscountType = match ($discountType) {
            '%' => 'percent',
            'currency' => 'fixed',
            default => null,
        };

        $itemAttributes = [
            'invoice_id' => $invoice->id,
            'title' => $itemData['title'],
            'type' => $type,
            'unit' => $itemData['unit'],
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'amount' => $finalAmount,
            'currency' => $invoice->currency,
            'description' => $itemData['description'] ?? null,
            'discount_value' => $discountValue,
        ];

        if ($mappedDiscountType) {
            $itemAttributes['discount_type'] = $mappedDiscountType;
        }

        return InvoiceItem::create($itemAttributes);
    }

    /**
     * Generate PDF for the invoice
     */
    private function generatePdf(Invoice $invoice): string
    {
        // Load relationships
        $invoice->load(['client', 'items', 'user.userCompany.companyOwner', 'bankAccount']);

        // Render HTML
        $html = view('pdf.invoice', compact('invoice'))->render();

        // Get CSS
        $cssPath = public_path('css/invoice/style.css');
        $css = file_exists($cssPath) ? file_get_contents($cssPath) : '';

        // Get Gotenberg URL
        $gotenbergUrl = config('services.gotenberg.url', 'http://localhost:3000');

        // Create Gotenberg request
        $request = Gotenberg::chromium($gotenbergUrl)
            ->pdf();

        // Add CSS if exists
        if (! empty($css)) {
            $request = $request->assets(Stream::string('style.css', $css));
        }

        // Add HTML
        $request = $request->html(Stream::string('index.html', $html));

        // Configure HTTP client
        $httpOptions = [
            'verify' => config('services.gotenberg.verify_ssl', false),
            'timeout' => 30,
        ];

        if ($apiKey = config('services.gotenberg.api_key')) {
            $httpOptions['headers'] = [
                'Authorization' => 'Basic '.$apiKey,
            ];
        }

        $httpClient = new HttpClient($httpOptions);

        // Generate PDF
        $response = Gotenberg::send($request, $httpClient);
        $pdfContent = $response->getBody()->getContents();

        // Save PDF to storage
        $filename = 'Faktura-'.str_replace('/', '-', $invoice->invoice_number).'.pdf';
        $storagePath = "invoices/{$invoice->user_id}/{$filename}";

        Storage::disk('public')->put($storagePath, $pdfContent);

        return storage_path('app/public/'.$storagePath);
    }

    /**
     * Send emails: invoice PDF and welcome email for new users
     * Returns reset URL for new users, null otherwise
     */
    private function sendEmail(User $user, Invoice $invoice, string $pdfPath, bool $isNewUser): ?string
    {
        // Always send invoice PDF immediately
        Mail::to($user->email)->send(
            new \App\Mail\PublicInvoiceGenerated($invoice, $pdfPath)
        );

        // Send welcome email with password reset link for new users
        if ($isNewUser) {
            $resetToken = Password::createToken($user);

            // Generate reset URL using config APP_URL
            $resetUrl = config('app.url').'/admin/password-reset/reset?'.http_build_query([
                'token' => $resetToken,
                'email' => $user->email,
            ]);

            // Send welcome email (can be queued for async delivery)
            Mail::to($user->email)->send(
                new \App\Mail\WelcomeNewUser($user, $resetToken)
            );

            return $resetUrl;
        }

        return null;
    }
}
