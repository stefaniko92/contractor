<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Knjiga o ostvarenom prometu - {{ $year }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9pt;
            line-height: 1.4;
            color: #000;
            padding: 20px;
        }

        .page-header {
            margin-bottom: 20px;
        }

        .company-box {
            border: 2px solid #000;
            padding: 12px;
            margin-bottom: 20px;
            max-width: 400px;
            line-height: 1.6;
        }

        .company-box .line {
            margin-bottom: 4px;
        }

        .company-box .label {
            font-weight: bold;
            display: inline-block;
            min-width: 120px;
        }

        .title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            margin: 20px 0;
            text-transform: uppercase;
        }

        .year-info {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 25px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .data-table th {
            background-color: #f0f0f0;
            border: 1px solid #000;
            padding: 8px 4px;
            font-size: 8pt;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
        }

        .data-table td {
            border: 1px solid #000;
            padding: 6px 4px;
            font-size: 8pt;
            vertical-align: middle;
        }

        .data-table .text-center {
            text-align: center;
        }

        .data-table .text-right {
            text-align: right;
        }

        .data-table .text-left {
            text-align: left;
        }

        .data-table .col-no {
            width: 5%;
            text-align: center;
        }

        .data-table .col-date-desc {
            width: 45%;
        }

        .data-table .col-amount {
            width: 25%;
            text-align: right;
        }

        .data-table .date-bold {
            font-weight: bold;
            margin-bottom: 3px;
        }

        .footer-section {
            margin-top: 30px;
            display: table;
            width: 100%;
        }

        .footer-left {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 20px;
        }

        .footer-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-left: 20px;
        }

        .total-box {
            border: 2px solid #000;
            padding: 15px;
            text-align: center;
        }

        .total-box .label {
            font-weight: bold;
            margin-bottom: 8px;
            font-size: 10pt;
        }

        .total-box .amount {
            font-size: 14pt;
            font-weight: bold;
            margin-top: 5px;
        }

        .signature-box {
            border: 2px solid #000;
            padding: 15px;
            text-align: center;
            min-height: 100px;
        }

        .signature-box .label {
            font-weight: bold;
            margin-bottom: 40px;
            font-size: 10pt;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin-top: 35px;
            padding-top: 5px;
            width: 200px;
            margin-left: auto;
            margin-right: auto;
        }

        .empty-row {
            height: 25px;
        }
    </style>
</head>
<body>
    {{-- Company Information Box --}}
    <div class="company-box">
        <div class="line"><span class="label">PIB:</span> {{ $company->company_tax_id ?? 'N/A' }}</div>
        <div class="line"><span class="label">Obveznik:</span> {{ $company->company_name ?? $user->name }}</div>
        <div class="line"><span class="label">Firma-radnje:</span> {{ $company->company_address ?? '' }}{{ $company->company_address_number ? ' ' . $company->company_address_number : '' }} {{ $company->company_city ?? '' }} {{ $company->company_postal_code ?? '' }}</div>
        <div class="line"><span class="label">Sedište:</span> {{ $company->company_municipality ?? $company->company_city ?? '' }}</div>
        <div class="line"><span class="label">MATIČNI BROJ:</span> {{ $company->company_registry_number ?? 'N/A' }}</div>
        <div class="line" style="margin-top: 8px;">{{ $company->company_activity_desc ?? '' }}</div>
    </div>

    {{-- Title --}}
    <div class="title">
        Knjiga o ostvarenom prometu<br>paušalno oporezovanih obveznika
    </div>

    <div class="year-info">
        Za {{ $year }}. godinu
    </div>

    {{-- Data Table --}}
    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" class="col-no">REDNI<br>BROJ</th>
                <th colspan="2">DATUM I OPIS KNJIŽENJA</th>
                <th colspan="2">PRIHOD DELATNOSTI OD IZVRŠENIH USLUGA</th>
                <th colspan="2">PRIHOD DELATNOSTI OD PRODAJE PROIZVODA</th>
            </tr>
            <tr>
                <th style="width: 12%;">Datum</th>
                <th style="width: 25%;">Opis</th>
                <th style="width: 10%;">U RSD</th>
                <th style="width: 11%;">U devizama</th>
                <th style="width: 10%;">U RSD</th>
                <th style="width: 11%;">U devizama</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoices as $index => $invoice)
                @php
                    // Calculate amounts by type
                    $serviceAmountRSD = 0;
                    $serviceAmountForeign = 0;
                    $productAmountRSD = 0;
                    $productAmountForeign = 0;

                    foreach($invoice->items as $item) {
                        if ($item->type === 'service') {
                            if ($invoice->currency === 'RSD') {
                                $serviceAmountRSD += $item->amount;
                            } else {
                                $serviceAmountForeign += $item->amount;
                            }
                        } elseif ($item->type === 'product') {
                            if ($invoice->currency === 'RSD') {
                                $productAmountRSD += $item->amount;
                            } else {
                                $productAmountForeign += $item->amount;
                            }
                        }
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $invoice->issue_date->format('d.m.Y') }}</td>
                    <td class="text-left">
                        @if($invoice->invoice_document_type === 'profaktura')
                            Profaktura
                        @elseif($invoice->invoice_document_type === 'avansna_faktura')
                            Avansna faktura
                        @else
                            Faktura
                        @endif
                        {{ $invoice->invoice_number }}
                        <br>
                        {{ $invoice->client->company_name ?? '' }}
                        @if($invoice->description)
                            <br><small>{{ $invoice->description }}</small>
                        @endif
                        @if($invoice->is_storno)
                            <br><strong>STORNO</strong>
                        @endif
                    </td>
                    {{-- Services --}}
                    <td class="text-right">
                        @if($serviceAmountRSD > 0)
                            {{ number_format($serviceAmountRSD, 2, ',', '.') }}
                        @endif
                    </td>
                    <td class="text-right">
                        @if($serviceAmountForeign > 0)
                            {{ number_format($serviceAmountForeign, 2, ',', '.') }} {{ $invoice->currency }}
                        @endif
                    </td>
                    {{-- Products --}}
                    <td class="text-right">
                        @if($productAmountRSD > 0)
                            {{ number_format($productAmountRSD, 2, ',', '.') }}
                        @endif
                    </td>
                    <td class="text-right">
                        @if($productAmountForeign > 0)
                            {{ number_format($productAmountForeign, 2, ',', '.') }} {{ $invoice->currency }}
                        @endif
                    </td>
                </tr>
            @endforeach

            {{-- Empty rows for formatting --}}
            @if($invoices->count() < 10)
                @for($i = $invoices->count(); $i < 10; $i++)
                    <tr class="empty-row">
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                @endfor
            @endif

            {{-- Total Row --}}
            @php
                // Calculate totals by type
                $totalServiceRSD = 0;
                $totalServiceForeign = [];
                $totalProductRSD = 0;
                $totalProductForeign = [];

                foreach($invoices as $invoice) {
                    foreach($invoice->items as $item) {
                        if ($item->type === 'service') {
                            if ($invoice->currency === 'RSD') {
                                $totalServiceRSD += $item->amount;
                            } else {
                                if (!isset($totalServiceForeign[$invoice->currency])) {
                                    $totalServiceForeign[$invoice->currency] = 0;
                                }
                                $totalServiceForeign[$invoice->currency] += $item->amount;
                            }
                        } elseif ($item->type === 'product') {
                            if ($invoice->currency === 'RSD') {
                                $totalProductRSD += $item->amount;
                            } else {
                                if (!isset($totalProductForeign[$invoice->currency])) {
                                    $totalProductForeign[$invoice->currency] = 0;
                                }
                                $totalProductForeign[$invoice->currency] += $item->amount;
                            }
                        }
                    }
                }
            @endphp
            <tr style="font-weight: bold; background-color: #f0f0f0;">
                <td colspan="3" class="text-right" style="padding-right: 10px;">UKUPNO:</td>
                <td class="text-right">
                    {{ number_format($totalServiceRSD, 2, ',', '.') }}
                </td>
                <td class="text-right">
                    @foreach($totalServiceForeign as $currency => $amount)
                        {{ number_format($amount, 2, ',', '.') }} {{ $currency }}
                        @if(!$loop->last)<br>@endif
                    @endforeach
                </td>
                <td class="text-right">
                    {{ number_format($totalProductRSD, 2, ',', '.') }}
                </td>
                <td class="text-right">
                    @foreach($totalProductForeign as $currency => $amount)
                        {{ number_format($amount, 2, ',', '.') }} {{ $currency }}
                        @if(!$loop->last)<br>@endif
                    @endforeach
                </td>
            </tr>
        </tbody>
    </table>

    {{-- Footer --}}
    <div class="footer-section">
        <div class="footer-left">
            <div class="total-box">
                <div class="label">UKUPAN PRIHOD</div>
                <div class="amount">{{ number_format($totalAmount, 2, ',', '.') }} RSD</div>
            </div>
        </div>
        <div class="footer-right">
            <div class="signature-box">
                <div class="label">Preduzetnik / Ovlašćeno lice</div>
                <div class="signature-line">
                    Potpis
                </div>
            </div>
        </div>
    </div>
</body>
</html>
