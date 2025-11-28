<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class TwelveMonthIncomeChart extends ChartWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static bool $isDiscovered = false;

    protected ?string $heading = 'Prihod u 12 meseci (8M limit)';

    protected function getData(): array
    {
        $userId = Auth::id();
        $twelveMonthsAgo = now()->subMonths(12);

        // Calculate income from domestic invoices in the last 12 months
        $twelveMonthIncome = Invoice::where('invoices.user_id', $userId)
            ->join('clients', 'invoices.client_id', '=', 'clients.id')
            ->where('clients.is_domestic', true)
            ->where('invoices.issue_date', '>=', $twelveMonthsAgo)
            ->whereNotNull('invoices.issue_date')
            ->sum('invoices.amount');

        $twelveMonthLimit = 8000000;
        $remaining = $twelveMonthLimit - $twelveMonthIncome;

        return [
            'datasets' => [
                [
                    'label' => 'Prihod',
                    'data' => [$twelveMonthIncome, max(0, $remaining)],
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
