<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class CurrentYearIncomeChart extends ChartWidget
{
    protected int | string | array $columnSpan = 1;

    protected ?string $heading = 'Godišnji prihod (6M limit)';

    protected function getData(): array
    {
        $userId = Auth::id();
        $currentYear = now()->year;

        // Calculate annual income from domestic invoices only
        $annualIncome = Invoice::where('invoices.user_id', $userId)
            ->join('clients', 'invoices.client_id', '=', 'clients.id')
            ->where('clients.is_domestic', true)
            ->whereYear('invoices.issue_date', $currentYear)
            ->whereNotNull('invoices.issue_date')
            ->sum('invoices.amount');

        $pausalaciLimit = 6000000;
        $remaining = $pausalaciLimit - $annualIncome;

        return [
            'datasets' => [
                [
                    'label' => 'Prihod',
                    'data' => [$annualIncome, max(0, $remaining)],
                    'backgroundColor' => [
                        'rgb(59, 130, 246)',
                        'rgb(229, 231, 235)',
                    ],
                ],
            ],
            'labels' => ['Ostvareno', 'Preostalo'],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}