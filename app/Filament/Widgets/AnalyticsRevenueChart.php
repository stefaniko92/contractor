<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\Analytics;
use App\Models\Invoice;
use Filament\Widgets\ChartWidget;

class AnalyticsRevenueChart extends ChartWidget
{
    protected static bool $isDiscovered = false;

    protected ?string $heading = 'Promet po mesecima';

    protected ?string $description = 'RSD fakture u odabranom periodu.';

    public string $period = 'year';

    public string $dateFrom = '';

    public string $dateTo = '';

    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $period = Analytics::periodBounds($this->period, $this->dateFrom, $this->dateTo);
        $monthlyIncome = Invoice::query()
            ->where('user_id', auth()->id())
            ->countsTowardsPausalIncome()
            ->where('currency', 'RSD')
            ->whereBetween('issue_date', [$period['start'], $period['end']])
            ->orderBy('issue_date')
            ->get()
            ->groupBy(fn (Invoice $invoice): string => $invoice->issue_date->format('Y-m'))
            ->map(fn ($invoices): float => (float) $invoices->sum('amount'));

        $labels = [];
        $amounts = [];
        $month = $period['start']->copy()->startOfMonth();

        while ($month->lte($period['end'])) {
            $labels[] = $month->translatedFormat('M Y');
            $amounts[] = $monthlyIncome->get($month->format('Y-m'), 0);
            $month->addMonth();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Fakturisano u RSD',
                    'data' => $amounts,
                    'borderColor' => '#2563eb',
                    'backgroundColor' => 'rgba(37, 99, 235, 0.12)',
                    'fill' => true,
                    'tension' => 0.35,
                    'pointBackgroundColor' => '#2563eb',
                    'pointBorderColor' => '#ffffff',
                    'pointBorderWidth' => 2,
                    'pointRadius' => 4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'x' => ['grid' => ['display' => false]],
                'y' => ['beginAtZero' => true, 'grid' => ['color' => 'rgba(148, 163, 184, 0.15)']],
            ],
        ];
    }
}
