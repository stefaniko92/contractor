<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class CurrentYearIncomeChart extends ChartWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static bool $isDiscovered = false;

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
        $percentageUsed = ($annualIncome / $pausalaciLimit) * 100;

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
                    'data' => [$annualIncome, max(0, $remaining)],
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
