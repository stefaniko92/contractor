<!DOCTYPE html>
<html lang="sr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $invoice->invoice_document_type === 'profaktura' ? 'Profaktura' : ($invoice->invoice_document_type === 'avansna_faktura' ? 'Avansna Faktura' : 'Faktura') }}</title>
    <link rel="stylesheet" href="style.css" type="text/css" media="all" />
    <style>
        .text-2xl {
            font-size: 1.5rem;
            line-height: 2rem;
        }
    </style>
</head>

<body>
<div>
    <div class="py-4">
        <div class="px-14 py-6">
            <table class="w-full border-collapse border-spacing-0">
                <tbody>
                <tr>
                    <td class="w-full align-top">
                        <div>
                            {{-- Company Logo Placeholder --}}
                            <h1 class="text-2xl font-bold text-main">{{ $invoice->user->company_name ?? 'SR Software' }}</h1>
                        </div>
                    </td>

                    <td class="align-top">
                        <div class="text-sm">
                            <table class="border-collapse border-spacing-0">
                                <tbody>
                                <tr>
                                    <td class="border-r pr-4">
                                        <div>
                                            <p class="whitespace-nowrap text-slate-400 text-right">Datum</p>
                                            <p class="whitespace-nowrap font-bold text-main text-right">{{ $invoice->issue_date->format('d.m.Y') }}</p>
                                        </div>
                                    </td>
                                    <td class="pl-4">
                                        <div>
                                            <p class="whitespace-nowrap text-slate-400 text-right">Broj fakture</p>
                                            <p class="whitespace-nowrap font-bold text-main text-right">{{ $invoice->invoice_number }}</p>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="bg-slate-100 px-14 py-6 text-sm">
            <table class="w-full border-collapse border-spacing-0">
                <tbody>
                <tr>
                    <td class="w-1/2 align-top">
                        <div class="text-sm text-neutral-600">
                            <p class="font-bold">{{ $invoice->user->company_name ?? 'SR Software Niš' }}</p>
                            @if($invoice->user->userCompany)
                                <p>PIB: {{ $invoice->user->userCompany->pib ?? 'N/A' }}</p>
                                <p>MB: {{ $invoice->user->userCompany->mb ?? 'N/A' }}</p>
                            @endif
                            <p>{{ $invoice->user->address ?? '' }}</p>
                            <p>{{ $invoice->user->email ?? '' }}</p>
                            @if($invoice->user->phone)
                                <p>Tel: {{ $invoice->user->phone }}</p>
                            @endif
                            @if($invoice->invoice_type === 'foreign' && $invoice->user)
                                @if($invoice->user->iban)
                                    <p>IBAN: {{ $invoice->user->iban }}</p>
                                @endif
                                @if($invoice->user->swift_code)
                                    <p>SWIFT: {{ $invoice->user->swift_code }}</p>
                                @endif
                            @endif
                        </div>
                    </td>
                    <td class="w-1/2 align-top text-right">
                        <div class="text-sm text-neutral-600">
                            <p class="font-bold">{{ $invoice->client->company_name }}</p>
                            @if($invoice->client->tax_id)
                                <p>PIB: {{ $invoice->client->tax_id }}</p>
                            @endif
                            @if($invoice->client->registration_number)
                                <p>MB: {{ $invoice->client->registration_number }}</p>
                            @endif
                            @if($invoice->client->vat_number)
                                <p>VAT: {{ $invoice->client->vat_number }}</p>
                            @endif
                            <p>{{ $invoice->client->address }}</p>
                            @if($invoice->client->city)
                                <p>{{ $invoice->client->city }}@if($invoice->client->country), {{ $invoice->client->country }}@endif</p>
                            @endif
                            @if($invoice->client->email)
                                <p>{{ $invoice->client->email }}</p>
                            @endif
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="px-14 py-10 text-sm text-neutral-700">
            <table class="w-full border-collapse border-spacing-0">
                <thead>
                <tr>
                    <td class="border-b-2 border-main pb-3 pl-3 font-bold text-main">#</td>
                    <td class="border-b-2 border-main pb-3 pl-2 font-bold text-main">Naziv</td>
                    <td class="border-b-2 border-main pb-3 pl-2 text-center font-bold text-main">Jed.</td>
                    <td class="border-b-2 border-main pb-3 pl-2 text-right font-bold text-main">Cena</td>
                    <td class="border-b-2 border-main pb-3 pl-2 text-center font-bold text-main">Kol.</td>
                    @if($invoice->items->some(fn($item) => $item->discount_value > 0))
                        <td class="border-b-2 border-main pb-3 pl-2 text-center font-bold text-main">Popust</td>
                    @endif
                    <td class="border-b-2 border-main pb-3 pl-2 pr-3 text-right font-bold text-main">Ukupno</td>
                </tr>
                </thead>
                <tbody>
                @foreach($invoice->items as $index => $item)
                <tr>
                    <td class="border-b py-3 pl-3">{{ $index + 1 }}.</td>
                    <td class="border-b py-3 pl-2">{{ $item->description }}</td>
                    <td class="border-b py-3 pl-2 text-center">{{ $item->unit }}</td>
                    <td class="border-b py-3 pl-2 text-right">{{ number_format($item->unit_price, 2) }} {{ $invoice->currency }}</td>
                    <td class="border-b py-3 pl-2 text-center">{{ $item->quantity }}</td>
                    @if($invoice->items->some(fn($i) => $i->discount_value > 0))
                        <td class="border-b py-3 pl-2 text-center">
                            @if($item->discount_value > 0)
                                {{ number_format($item->discount_value, 2) }}{{ $item->discount_type === 'percent' ? '%' : ' ' . $invoice->currency }}
                            @else
                                -
                            @endif
                        </td>
                    @endif
                    <td class="border-b py-3 pl-2 pr-3 text-right">{{ number_format($item->amount, 2) }} {{ $invoice->currency }}</td>
                </tr>
                @endforeach
                <tr>
                    <td colspan="{{ $invoice->items->some(fn($item) => $item->discount_value > 0) ? '7' : '6' }}">
                        <table class="w-full border-collapse border-spacing-0">
                            <tbody>
                            <tr>
                                <td class="w-full"></td>
                                <td>
                                    <table class="w-full border-collapse border-spacing-0">
                                        <tbody>
                                        <tr>
                                            <td class="bg-main p-3">
                                                <div class="whitespace-nowrap font-bold text-white">Ukupno:</div>
                                            </td>
                                            <td class="bg-main p-3 text-right">
                                                <div class="whitespace-nowrap font-bold text-white">{{ number_format($invoice->amount, 2) }} {{ $invoice->currency }}</div>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        @if($invoice->bankAccount)
            <div class="px-14 text-sm text-neutral-700">
                <p class="text-main font-bold">PODACI ZA PLAĆANJE</p>
                @if($invoice->bankAccount->bank_name)
                    <p>{{ $invoice->bankAccount->bank_name }}</p>
                @endif
                @if($invoice->bankAccount->account_number)
                    <p>Broj računa: {{ $invoice->bankAccount->account_number }}</p>
                @endif
                @if($invoice->bankAccount->swift && $invoice->invoice_type === 'foreign')
                    <p>SWIFT: {{ $invoice->bankAccount->swift }}</p>
                @endif
                <p>Poziv na broj: {{ $invoice->invoice_number }}</p>
                @if($invoice->due_date)
                    <p>Rok plaćanja: {{ $invoice->due_date->format('d.m.Y') }}</p>
                @endif
            </div>
        @endif

        @if($invoice->description)
            <div class="px-14 py-10 text-sm text-neutral-700">
                <p class="text-main font-bold">Napomene</p>
                <p class="italic">{{ $invoice->description }}</p>
            </div>
        @endif

        <footer class="fixed bottom-0 left-0 bg-slate-100 w-full text-neutral-600 text-center text-xs py-3">
            {{ $invoice->user->company_name ?? 'SR Software' }}
            @if($invoice->user->email)
                <span class="text-slate-300 px-2">|</span>
                {{ $invoice->user->email }}
            @endif
            @if($invoice->user->phone)
                <span class="text-slate-300 px-2">|</span>
                {{ $invoice->user->phone }}
            @endif
        </footer>
    </div>
</div>
</body>

</html>
