<?php

namespace App\Filament\Pages;

use App\Models\Invoice;
use App\PausalIncomeAnalytics;
use BackedEnum;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

class Analytics extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected string $view = 'filament.pages.analytics';

    protected static ?string $title = 'Analitika';

    protected static ?string $navigationLabel = 'Analitika';

    protected static string|\UnitEnum|null $navigationGroup = 'Fakturisanje';

    protected static ?int $navigationSort = 10;

    public string $period = 'year';

    public string $dateFrom = '';

    public string $dateTo = '';

    public string $appliedDateFrom = '';

    public string $appliedDateTo = '';

    public function mount(): void
    {
        $this->dateFrom = $this->appliedDateFrom = now()->startOfYear()->toDateString();
        $this->dateTo = $this->appliedDateTo = now()->toDateString();
    }

    public function applyDateRange(): void
    {
        $this->validate([
            'dateFrom' => ['required', 'date_format:Y-m-d'],
            'dateTo' => ['required', 'date_format:Y-m-d', 'after_or_equal:dateFrom'],
        ], [
            'dateFrom.required' => 'Izaberite početni datum.',
            'dateFrom.date_format' => 'Unesite ispravan početni datum.',
            'dateTo.required' => 'Izaberite krajnji datum.',
            'dateTo.date_format' => 'Unesite ispravan krajnji datum.',
            'dateTo.after_or_equal' => 'Krajnji datum mora biti jednak ili posle početnog datuma.',
        ]);

        $this->appliedDateFrom = $this->dateFrom;
        $this->appliedDateTo = $this->dateTo;
        $this->period = 'custom';
    }

    /**
     * @return array{label: string, start: Carbon, end: Carbon}
     */
    public function getSelectedPeriod(): array
    {
        return static::periodBounds($this->period, $this->appliedDateFrom, $this->appliedDateTo);
    }

    /**
     * @return array{label: string, start: Carbon, end: Carbon}
     */
    public static function periodBounds(string $period, string $dateFrom = '', string $dateTo = ''): array
    {
        $end = now()->endOfDay();

        if ($period === 'custom') {
            validator(['dateFrom' => $dateFrom, 'dateTo' => $dateTo], [
                'dateFrom' => ['required', 'date_format:Y-m-d'],
                'dateTo' => ['required', 'date_format:Y-m-d', 'after_or_equal:dateFrom'],
            ])->validate();

            return [
                'label' => 'Prilagođeni period',
                'start' => Carbon::parse($dateFrom)->startOfDay(),
                'end' => Carbon::parse($dateTo)->endOfDay(),
            ];
        }

        return match ($period) {
            '30_days' => ['label' => 'Poslednjih 30 dana', 'start' => $end->copy()->subDays(29)->startOfDay(), 'end' => $end],
            '90_days' => ['label' => 'Poslednjih 90 dana', 'start' => $end->copy()->subDays(89)->startOfDay(), 'end' => $end],
            '365_days' => ['label' => 'Poslednjih 365 dana', 'start' => $end->copy()->subDays(364)->startOfDay(), 'end' => $end],
            default => ['label' => 'Tekuća godina', 'start' => $end->copy()->startOfYear(), 'end' => $end],
        };
    }

    /**
     * @return array{
     *     period: array{label: string, start: Carbon, end: Carbon},
     *     invoiced: string,
     *     paid: string,
     *     outstanding: string,
     *     invoice_count: int,
     *     overdue_count: int,
     *     overdue: string,
     *     currencies: Collection<int, array{currency: string, amount: float, invoices: int}>,
     *     clients: Collection<int, array{name: string, invoices: int, amount: string}>,
     *     pausal_6m: array{income: float, remaining: float, percentage: float},
     *     pausal_8m: array{income: float, remaining: float, percentage: float}
     * }
     */
    public function getAnalytics(): array
    {
        $period = $this->getSelectedPeriod();
        $invoices = Invoice::query()
            ->with('client:id,company_name')
            ->where('user_id', auth()->id())
            ->countsTowardsPausalIncome()
            ->whereBetween('issue_date', [$period['start'], $period['end']])
            ->orderBy('issue_date')
            ->get();

        $paidInvoices = $invoices->whereIn('status', ['charged', 'paid']);
        $outstandingInvoices = $invoices->whereIn('status', ['issued', 'sent', 'uncharged', 'unpaid']);
        $overdueInvoices = $outstandingInvoices->filter(fn (Invoice $invoice): bool => $invoice->due_date?->isPast() ?? false);
        $currencyTotals = $this->currencyTotals($invoices);

        $clients = $invoices
            ->groupBy('client_id')
            ->map(function (Collection $clientInvoices): array {
                return [
                    'name' => $clientInvoices->first()->client?->company_name ?? 'Obrisan klijent',
                    'invoices' => $clientInvoices->count(),
                    'amount' => $this->formatCurrencyTotals($this->currencyTotals($clientInvoices)),
                    'sort_amount' => (float) $clientInvoices->where('currency', 'RSD')->sum('amount'),
                ];
            })
            ->sortByDesc('sort_amount')
            ->take(5)
            ->values()
            ->map(fn (array $client): array => collect($client)->except('sort_amount')->all());

        $currentYearDomesticIncome = Invoice::query()
            ->where('invoices.user_id', auth()->id())
            ->countsTowardsPausalIncome()
            ->join('clients', 'invoices.client_id', '=', 'clients.id')
            ->where('clients.is_domestic', true)
            ->whereBetween('invoices.issue_date', [now()->startOfYear(), now()->endOfDay()])
            ->sum('invoices.amount');
        $pausal8m = app(PausalIncomeAnalytics::class)->forDate(auth()->id(), now());

        return [
            'period' => $period,
            'invoiced' => $this->formatCurrencyTotals($currencyTotals),
            'paid' => $this->formatCurrencyTotals($this->currencyTotals($paidInvoices)),
            'outstanding' => $this->formatCurrencyTotals($this->currencyTotals($outstandingInvoices)),
            'invoice_count' => $invoices->count(),
            'overdue_count' => $overdueInvoices->count(),
            'overdue' => $this->formatCurrencyTotals($this->currencyTotals($overdueInvoices)),
            'currencies' => $currencyTotals->sortByDesc('amount')->values(),
            'clients' => $clients,
            'pausal_6m' => [
                'income' => (float) $currentYearDomesticIncome,
                'remaining' => 6_000_000 - (float) $currentYearDomesticIncome,
                'percentage' => ((float) $currentYearDomesticIncome / 6_000_000) * 100,
            ],
            'pausal_8m' => [
                'income' => $pausal8m['income'],
                'remaining' => $pausal8m['remaining'],
                'percentage' => $pausal8m['percentage_used'],
            ],
        ];
    }

    /**
     * @param  Collection<int, Invoice>  $invoices
     * @return Collection<int, array{currency: string, amount: float, invoices: int}>
     */
    private function currencyTotals(Collection $invoices): Collection
    {
        return $invoices
            ->groupBy('currency')
            ->map(fn (Collection $currencyInvoices, string $currency): array => [
                'currency' => $currency,
                'amount' => (float) $currencyInvoices->sum('amount'),
                'invoices' => $currencyInvoices->count(),
            ])
            ->values();
    }

    /**
     * @param  Collection<int, array{currency: string, amount: float, invoices: int}>  $totals
     */
    private function formatCurrencyTotals(Collection $totals): string
    {
        if ($totals->isEmpty()) {
            return 'Nema podataka';
        }

        return $totals
            ->sortByDesc('amount')
            ->map(fn (array $total): string => number_format($total['amount'], 0, ',', '.').' '.$total['currency'])
            ->implode(' · ');
    }
}
