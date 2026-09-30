<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\Analytics;
use App\Models\Invoice;
use Filament\Widgets\ChartWidget;

class AnalyticsInvoiceStatusChart extends ChartWidget
{
    protected static bool $isDiscovered = false;

    protected ?string $heading = 'Status naplate';

    protected ?string $description = 'Broj faktura po statusu u odabranom periodu.';

    public string $period = 'year';

    public string $dateFrom = '';

    public string $dateTo = '';

    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $period = Analytics::periodBounds($this->period, $this->dateFrom, $this->dateTo);
        $invoices = Invoice::query()
            ->where('user_id', auth()->id())
            ->countsTowardsPausalIncome()
            ->whereBetween('issue_date', [$period['start'], $period['end']])
            ->get();
        $paid = $invoices->whereIn('status', ['charged', 'paid'])->count();
        $open = $invoices->whereIn('status', ['issued', 'sent', 'uncharged', 'unpaid']);
        $overdue = $open->filter(fn (Invoice $invoice): bool => $invoice->due_date?->isPast() ?? false)->count();

        return [
            'datasets' => [
                [
                    'data' => [$paid, $open->count() - $overdue, $overdue],
                    'backgroundColor' => ['#16a34a', '#f59e0b', '#dc2626'],
                    'borderColor' => ['#16a34a', '#f59e0b', '#dc2626'],
                    'borderWidth' => 0,
                    'hoverOffset' => 8,
                ],
            ],
            'labels' => ['Naplaćeno', 'Otvoreno', 'Preko roka'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'cutout' => '68%',
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels' => ['usePointStyle' => true, 'padding' => 18],
                ],
            ],
        ];
    }
}
