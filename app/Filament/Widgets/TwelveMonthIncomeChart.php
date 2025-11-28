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
        $percentageUsed = ($twelveMonthIncome / $twelveMonthLimit) * 100;

        // Choose color based on usage percentage
        $fillColor = 'rgb(34, 197, 94)'; // Green for safe
        if ($percentageUsed > 80) {
            $fillColor = 'rgb(239, 68, 68)'; // Red for danger
        } elseif ($percentageUsed > 60) {
            $fillColor = 'rgb(251, 146, 60)'; // Orange for warning
        }

        return [
            'datasets' => [
                [
                    'label' => 'Prihod',
                    'data' => [$twelveMonthIncome, max(0, $remaining)],
                    'backgroundColor' => [
                        $fillColor,
                        'rgb(226, 232, 240)',
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

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'right',
                ],
            ],
            'maintainAspectRatio' => true,
            'aspectRatio' => 2,
        ];
    }
}
