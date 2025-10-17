@php
    $user = $invoice->user;
    $company = $user->userCompany;
    $client = $invoice->client;
    $bankAccount = $invoice->bankAccount;

    // Set locale based on client type (domestic = Serbian, foreign = English)
    $isDomestic = $client->is_domestic === 1 || $client->is_domestic === true || $client->is_domestic === '1';
    $locale = $isDomestic ? 'sr' : 'en';
    app()->setLocale($locale);

    // Determine document title
    $documentTitle = match($invoice->invoice_document_type) {
        'profaktura' => __('invoice_pdf.proforma'),
        'avansna_faktura' => __('invoice_pdf.advance_invoice'),
        default => __('invoice_pdf.invoice')
    };

    // Calculate totals
    $subtotal = $invoice->items->sum(function($item) {
        return $item->quantity * $item->unit_price;
    });

    $discountAmount = $invoice->items->sum(function($item) {
        $itemTotal = $item->quantity * $item->unit_price;
        if ($item->discount_type === 'percent') {
            return $itemTotal * ($item->discount_value / 100);
        }
        return $item->discount_value ?? 0;
    });

    $total = $subtotal - $discountAmount;

    // Get logo or initials
    $logoPath = $company?->company_logo_path ?? $user->logo_path;
    $companyName = $company?->company_name ?? $user->company_name ?? $user->name;

    // Convert logo to base64 for PDF embedding
    $logoBase64 = null;
    if ($logoPath) {
        // Handle array from FileUpload component (in case it wasn't converted)
        if (is_array($logoPath)) {
            $logoPath = $logoPath[0] ?? null;
        }

        if ($logoPath) {
            // Try multiple possible storage locations
            $possiblePaths = [
                storage_path('app/private/' . $logoPath),  // Filament 4 default
                storage_path('app/public/' . $logoPath),   // Filament 3 default
                storage_path('app/' . $logoPath),          // Direct storage
            ];

            foreach ($possiblePaths as $fullLogoPath) {
                if (file_exists($fullLogoPath)) {
                    try {
                        $imageData = file_get_contents($fullLogoPath);
                        $mimeType = mime_content_type($fullLogoPath);
                        $logoBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
                        break;
                    } catch (\Exception $e) {
                        // Continue to next path
                        continue;
                    }
                }
            }
        }
    }

    // Get initials for fallback
    $words = explode(' ', $companyName);
    $initials = '';
    foreach ($words as $word) {
        if (!empty($word)) {
            $initials .= mb_strtoupper(mb_substr($word, 0, 1));
            if (mb_strlen($initials) >= 2) break;
        }
    }
    if (mb_strlen($initials) < 2 && mb_strlen($companyName) > 0) {
        $initials = mb_strtoupper(mb_substr($companyName, 0, 2));
    }

    // Calculate payment days
    $paymentDays = $invoice->issue_date && $invoice->due_date
        ? $invoice->issue_date->diffInDays($invoice->due_date)
        : 30;

    // Generate QR code for NBS IPS payment
    $qrCode = null;

    // Try to get bank account - from invoice or fallback to primary company account
    $paymentBankAccount = $bankAccount;
    if (!$paymentBankAccount && $company) {
        // Try to get primary bank account for the company
        $paymentBankAccount = $company->bankAccounts()->where('is_primary', true)->first();
        // If no primary, get any bank account
        if (!$paymentBankAccount) {
            $paymentBankAccount = $company->bankAccounts()->first();
        }
    }

    // Generate QR code for domestic invoices with bank account
    if ($paymentBankAccount && $isDomestic) {
        try {
            $qrCode = \App\Helpers\NbsQrCodeHelper::generateQrCodeBase64([
                'recipient_account' => $paymentBankAccount->account_number,
                'recipient_name' => $companyName,
                'amount' => $total,
                'payer_name' => $client->company_name,
                'payment_code' => '289',
                'purpose' => '',
                'model' => '97',
                'reference_number' => $invoice->invoice_number,
            ], 120);
        } catch (\Exception $e) {
            \Log::error('QR code generation failed: ' . $e->getMessage());
            $qrCode = null;
        }
    }
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $documentTitle }} #{{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
            padding: 40px;
            background: #fff;
        }

        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
        }

        /* Header */
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 3px solid #e74c3c;
        }

        .company-logo {
            font-size: 32px;
            font-weight: bold;
            color: #e74c3c;
        }

        .company-logo img {
            max-width: 150px;
            max-height: 60px;
            object-fit: contain;
        }

        .company-name {
            font-size: 18px;
            color: #666;
            margin-top: 5px;
        }

        .invoice-title {
            text-align: right;
        }

        .invoice-number {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .invoice-dates {
            color: #666;
            font-size: 12px;
            display: flex;
            flex-direction: column;
            gap: 5px;
            align-items: flex-end;
        }

        .date-row {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .date-label {
            font-weight: 500;
            white-space: nowrap;
        }

        .date-separator {
            margin: 0 8px;
            color: #999;
        }

        /* Parties Section */
        .parties-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            gap: 20px;
        }

        .party-box {
            flex: 1;
        }

        .party-title {
            font-weight: 600;
            color: #666;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 10px;
            letter-spacing: 0.5px;
        }

        .party-name {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
        }

        .party-details {
            color: #666;
            font-size: 13px;
            line-height: 1.8;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .items-table thead {
            background: #f8f9fa;
        }

        .items-table th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #666;
            font-size: 12px;
            text-transform: uppercase;
            border-bottom: 2px solid #dee2e6;
        }

        .items-table th.text-right {
            text-align: right;
        }

        .items-table td {
            padding: 15px 12px;
            border-bottom: 1px solid #e9ecef;
            color: #333;
        }

        .items-table td.text-right {
            text-align: right;
        }

        .items-table tbody tr:hover {
            background: #f8f9fa;
        }

        /* Totals Section */
        .totals-section {
            display: flex;
            justify-content: flex-end;
            align-items: flex-start;
            margin-bottom: 20px;
            gap: 20px;
        }

        .totals-section.with-qr {
            justify-content: space-between;
        }

        .qr-code-box {
            flex: 0 0 auto;
        }

        .qr-code-box img {
            display: block;
            width: 120px;
            height: 120px;
        }

        .qr-code-box p {
            text-align: center;
            font-size: 10px;
            color: #666;
            margin-top: 5px;
        }

        .totals-box {
            width: 350px;
            flex: 0 0 350px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .total-row.subtotal {
            font-size: 14px;
        }

        .total-row.discount {
            font-size: 14px;
            color: #666;
        }

        .total-row.grand-total {
            font-size: 18px;
            font-weight: bold;
            border-top: 2px solid #333;
            border-bottom: 3px double #333;
            padding: 10px 0;
            margin-top: 10px;
            color: #e74c3c;
        }

        /* Notes Section */
        .notes-section {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .notes-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            font-size: 13px;
            text-transform: uppercase;
        }

        .note-item {
            color: #666;
            font-size: 13px;
            margin: 5px 0;
            line-height: 1.6;
        }

        /* Tax Note */
        .tax-note {
            border-left: 3px solid #e74c3c;
            padding: 15px;
            background: #fff3f3;
            margin-bottom: 20px;
        }

        .tax-note-title {
            font-weight: 600;
            color: #e74c3c;
            margin-bottom: 8px;
            font-size: 13px;
        }

        .tax-note-text {
            color: #666;
            font-size: 12px;
            line-height: 1.6;
        }

        /* Footer */
        .invoice-footer {
            text-align: center;
            padding-top: 0;
            border-top: 1px solid #e9ecef;
            color: #999;
            font-size: 12px;
        }

        /* Print Styles */
        @media print {
            body {
                padding: 0;
            }

            .invoice-container {
                max-width: 100%;
            }

            .items-table tbody tr:hover {
                background: transparent;
            }

            @page {
                margin: 20mm;
            }
        }

        /* Utility Classes */
        .text-bold {
            font-weight: 600;
        }

        .text-muted {
            color: #999;
        }
    </style>
</head>
<body>
<div class="invoice-container">
    <!-- Header -->
    <div class="invoice-header">
        <div>
            <div class="company-logo">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="{{ $companyName }}">
                @else
                    {{ $initials }}
                    <div class="company-name">{{ $companyName }}</div>
                @endif
            </div>
        </div>
        <div class="invoice-title">
            <div class="invoice-number">{{ $documentTitle }}: {{ $invoice->invoice_number }}</div>
            <div class="invoice-dates">
                <div class="date-row">
                    <span class="date-label">{{ __('invoice_pdf.invoice_date') }}:</span>
                    <span>{{ $invoice->issue_date?->format('d.m.Y') }}</span>
                    <span class="date-separator">|</span>
                    <span class="date-label">{{ __('invoice_pdf.transaction_date') }}:</span>
                    <span>{{ $invoice->due_date?->format('d.m.Y') }}</span>
                    <span class="date-separator">|</span>
                    <span class="date-label">{{ __('invoice_pdf.place') }}:</span>
                    <span>{{ $invoice->trading_place }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- From/To Section -->
    <div class="parties-section">
        <div class="party-box">
            <div class="party-title">{{ __('invoice_pdf.from') }}:</div>
            <div class="party-name">{{ $companyName }}</div>
            <div class="party-details">
                @if($company?->company_address)
                    {{ $company->company_address }}@if($company->company_address_number), {{ $company->company_address_number }}@endif<br>
                @elseif($user->address)
                    {{ $user->address }}<br>
                @endif
                @if($company?->company_city)
                    {{ $company->company_city }} @if($company->company_postal_code){{ $company->company_postal_code }}@endif<br>
                @endif
                @if($company?->company_tax_id)
                    <span class="text-bold">{{ __('invoice_pdf.pib') }}:</span> {{ $company->company_tax_id }}<br>
                @elseif($user->tax_id)
                    <span class="text-bold">{{ __('invoice_pdf.pib') }}:</span> {{ $user->tax_id }}<br>
                @endif
                @if($company?->company_registry_number)
                    <span class="text-bold">{{ __('invoice_pdf.reg_number') }}:</span> {{ $company->company_registry_number }}<br>
                @endif
                @if($bankAccount)
                    <span class="text-bold">{{ __('invoice_pdf.bank_account') }}:</span>
                    {{ $bankAccount->account_type === 'foreign' ? ($bankAccount->iban ?? 'N/A') : ($bankAccount->account_number ?? 'N/A') }}<br>
                @elseif($user->iban)
                    <span class="text-bold">{{ __('invoice_pdf.bank_account') }}:</span> {{ $user->iban }}<br>
                @endif
                @if($company?->show_email_on_invoice && $company?->company_email)
                    <span class="text-bold">{{ __('invoice_pdf.email') }}:</span> {{ $company->company_email }}
                @elseif($user->email)
                    <span class="text-bold">{{ __('invoice_pdf.email') }}:</span> {{ $user->email }}
                @endif
            </div>
        </div>

        <div class="party-box">
            <div class="party-title">{{ __('invoice_pdf.to') }}:</div>
            <div class="party-name">{{ $client->company_name }}</div>
            <div class="party-details">
                @if($client->address)
                    {{ $client->address }}<br>
                @endif
                @if($client->city)
                    {{ $client->city }}@if($client->country), {{ $client->country }}@endif<br>
                @endif
                @if($client->tax_id)
                    <span class="text-bold">{{ __('invoice_pdf.pib') }}:</span> {{ $client->tax_id }}<br>
                @endif
                @if($client->registration_number)
                    <span class="text-bold">{{ __('invoice_pdf.reg_number') }}:</span> {{ $client->registration_number }}
                @endif
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
        <tr>
            <th>{{ __('invoice_pdf.table.service_description') }}</th>
            <th>{{ __('invoice_pdf.table.unit') }}</th>
            <th class="text-right">{{ __('invoice_pdf.table.quantity') }}</th>
            <th class="text-right">{{ __('invoice_pdf.table.price') }}</th>
            <th class="text-right">{{ __('invoice_pdf.table.discount') }}</th>
            <th class="text-right">{{ __('invoice_pdf.table.total') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($invoice->items as $item)
            @php
                $itemTotal = $item->quantity * $item->unit_price;
                $itemDiscount = 0;
                $itemDiscountDisplay = '';

                if ($item->discount_value) {
                    if ($item->discount_type === 'percent') {
                        $itemDiscount = $itemTotal * ($item->discount_value / 100);
                        $itemDiscountDisplay = number_format($item->discount_value, 2) . '%';
                    } else {
                        $itemDiscount = $item->discount_value;
                        $itemDiscountDisplay = number_format($item->discount_value, 2) . ' ' . ($item->currency ?? $invoice->currency ?? 'RSD');
                    }
                }

                $itemFinalTotal = $itemTotal - $itemDiscount;
            @endphp
            <tr>
                <td>{{ $item->description ?? $item->title }}</td>
                <td>{{ $item->unit }}</td>
                <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right">{{ $itemDiscountDisplay }}</td>
                <td class="text-right text-bold">{{ number_format($itemFinalTotal, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <div class="totals-section {{ $qrCode ? 'with-qr' : '' }}">
        @if($qrCode)
        <div class="qr-code-box">
            <img src="{{ $qrCode }}" alt="QR Code for Payment">
            <p>NBS IPS QR</p>
        </div>
        @endif
        <div class="totals-box">
            <div class="total-row subtotal">
                <span>{{ __('invoice_pdf.totals.subtotal') }} ({{ $invoice->currency ?? 'RSD' }})</span>
                <span class="text-bold">{{ number_format($subtotal, 2) }}</span>
            </div>
            @if($discountAmount > 0)
            <div class="total-row discount">
                <span>{{ __('invoice_pdf.totals.discount') }} ({{ $invoice->currency ?? 'RSD' }})</span>
                <span>-{{ number_format($discountAmount, 2) }}</span>
            </div>
            @endif
            <div class="total-row grand-total">
                <span>{{ __('invoice_pdf.totals.total_amount') }} ({{ $invoice->currency ?? 'RSD' }})</span>
                <span>{{ number_format($total, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Notes -->
    <div class="notes-section">
        <div class="note-item"><strong>{{ __('invoice_pdf.payment.payment_reference_note', ['number' => $invoice->invoice_number]) }}</strong></div>
        @if($company)
            @php
                $invoiceNote = $isDomestic ? $company->invoice_note_domestic : $company->invoice_note_foreign;
            @endphp
            @if($invoiceNote)
                <div class="note-item">{{ $invoiceNote }}</div>
            @endif
        @endif
        <div class="note-item">{{ __('invoice_pdf.payment.valid_without_signature') }}</div>
        <div class="note-item">{{ __('invoice_pdf.payment.invoice_id') }}: <span class="text-muted">{{ $invoice->id }}</span></div>
    </div>

    <!-- Tax Information -->
    <div class="tax-note">
        <div class="tax-note-title">{{ __('invoice_pdf.tax_notice.title') }}</div>
        <div class="tax-note-text">
            {!! __('invoice_pdf.tax_notice.message') !!}
        </div>
        <div class="tax-note-text" style="margin-top: 10px;">
            <strong>{{ __('invoice_pdf.payment.issue_place') }}:</strong> {{ $invoice->trading_place }}
        </div>
    </div>

    <!-- Footer -->
    <div class="invoice-footer">
        {{ __('invoice_pdf.footer', ['platform' => config('app.name', 'Pausalci')]) }}
    </div>
</div>
</body>
</html>